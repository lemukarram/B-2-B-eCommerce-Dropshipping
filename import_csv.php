<?php

declare(strict_types=1);

/**
 * EMAG.PK BROWSER-OPTIMIZED MIGRATION SCRIPT
 */

// 1. Setup paths
define('BASE_PATH',    __DIR__ . '/src');
define('CORE_PATH',    BASE_PATH . '/core');
define('PUBLIC_PATH',  __DIR__);

// 2. Load Autoloader
if (!file_exists(CORE_PATH . '/Autoloader.php')) {
    die("Error: System core files missing.");
}
require CORE_PATH . '/Autoloader.php';
Autoloader::register();

use App\Services\SlugService;
use Core\Database;

// 3. Set Limits
set_time_limit(0); 
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Function to show progress in browser
function showProgress($msg) {
    echo $msg . "<br>";
    if (ob_get_level() > 0) ob_flush();
    flush();
}

echo "<html><body style='font-family:sans-serif; background:#f4f4f4; padding:20px;'>";
echo "<h2>EMAG.PK Migration Progress</h2><hr>";

$slugService = new SlugService();

try {
    $cfg = require BASE_PATH . '/config/database.php';
    $pdo = Database::getInstance();
    
    showProgress("✔ Database Connected.");

    // --- STEP 1: FRESH START ---
    showProgress("⌛ Wiping existing data...");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE product_images;");
    $pdo->exec("TRUNCATE TABLE products;");
    $pdo->exec("TRUNCATE TABLE categories;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    showProgress("✔ Database is now empty.");

} catch (Exception $e) {
    die("<div style='color:red;'>Fatal Error: " . $e->getMessage() . "</div>");
}

// --- STEP 2: OPEN CSV ---
$csvFile = __DIR__ . '/csv-emag-file.csv';
if (!file_exists($csvFile)) {
    die("<div style='color:red;'>Error: File 'csv-emag-file.csv' not found in the root folder.</div>");
}

$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle); 
if (!$headers) die("Error: CSV is empty.");
$headers = array_map('trim', $headers);

$productsCount = 0;
$categoriesCache = [];

function clean($text) {
    return trim(strip_tags((string)$text));
}

function download_image($url) {
    $url = trim($url);
    if (empty($url)) return null;
    $path = parse_url($url, PHP_URL_PATH);
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'jpg';
    $filename = bin2hex(random_bytes(10)) . '.' . $ext;
    $dest = PUBLIC_PATH . '/uploads/products/' . $filename;
    
    if (!is_dir(PUBLIC_PATH . '/uploads/products')) {
        mkdir(PUBLIC_PATH . '/uploads/products', 0755, true);
    }

    $data = @file_get_contents($url);
    if ($data) {
        file_put_contents($dest, $data);
        return '/uploads/products/' . $filename;
    }
    return null;
}

function process_categories($catString, &$cache, $pdo) {
    if (empty($catString)) return null;
    $paths = explode(',', $catString);
    $firstId = null;

    foreach ($paths as $path) {
        $parts = explode('>', $path);
        $parentId = null;
        foreach ($parts as $part) {
            $name = clean($part);
            if (empty($name)) continue;
            $key = ($parentId ?? 'root') . '_' . strtolower($name);
            if (isset($cache[$key])) {
                $parentId = $cache[$key];
            } else {
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND (parent_id = ? OR (? IS NULL AND parent_id IS NULL)) LIMIT 1");
                $stmt->execute([$name, $parentId, $parentId]);
                $row = $stmt->fetch();
                if ($row) {
                    $parentId = (int)$row['id'];
                } else {
                    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, parent_id, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$name, trim($slug, '-'), $parentId]);
                    $parentId = (int)$pdo->lastInsertId();
                }
                $cache[$key] = $parentId;
            }
            if (!$firstId) $firstId = $parentId;
        }
    }
    return $firstId;
}

showProgress("⌛ Starting Import of products...");

while (($data = fgetcsv($handle)) !== false) {
    if (count($data) !== count($headers)) continue;
    
    $row = array_combine($headers, $data);
    $title = clean($row['Name'] ?? '');
    if (empty($title)) continue;

    $sku = !empty($row['SKU']) ? clean($row['SKU']) : 'WC-' . rand(1000, 9999);
    $description = trim($row['Description'] ?? $row['Short description'] ?? '');
    $price = (float)preg_replace('/[^0-9.]/', '', (string)($row['Sale price'] ?: $row['Regular price']));
    $stock = (trim($row['In stock?']) === '1') ? (!empty($row['Stock']) ? (int)$row['Stock'] : 100) : 0;
    
    $catId = process_categories((string)($row['Categories'] ?? ''), $categoriesCache, $pdo);
    $slug = $slugService->generate($title);

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, title, slug, description, base_price, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$catId, $sku, $title, $slug, $description, $price, $stock]);
        $productId = (int)$pdo->lastInsertId();

        $images = explode(',', (string)($row['Images'] ?? ''));
        foreach ($images as $idx => $url) {
            $local = download_image($url);
            if ($local) {
                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$productId, $local, ($idx === 0 ? 1 : 0), $idx]);
            }
        }
        $pdo->commit();
        $productsCount++;
        if ($productsCount % 5 === 0) {
            showProgress("✔ Imported {$productsCount} products...");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        showProgress("<span style='color:orange;'>✘ Failed: {$title}</span>");
    }
}

fclose($handle);
echo "<h3>✔ Migration Completed! Total Products: {$productsCount}</h3>";
echo "<a href='/admin/products'>Click here to view products</a>";
echo "</body></html>";
