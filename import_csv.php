<?php

declare(strict_types=1);

/**
 * EMAG.PK DEFINITIVE MIGRATION SCRIPT
 * Verified for Hostinger / Shared Hosting
 */

// 1. Error Reporting & Logging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 2. Define System Paths (Crucial for Autoloader & Database)
define('BASE_PATH',    __DIR__ . '/src');
define('APP_PATH',     BASE_PATH . '/app');
define('CORE_PATH',    BASE_PATH . '/core');
define('VIEW_PATH',    BASE_PATH . '/views');
define('CONFIG_PATH',  BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PUBLIC_PATH',  __DIR__);

// 3. System Initialization
try {
    if (!file_exists(CORE_PATH . '/Autoloader.php')) {
        throw new Exception("Core Autoloader not found. Path: " . CORE_PATH);
    }
    require CORE_PATH . '/Autoloader.php';
    Autoloader::register();

    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception("config.php missing in root directory.");
    }
} catch (Exception $e) {
    die("<div style='color:red; font-family:sans-serif;'><b>System Error:</b> " . $e->getMessage() . "</div>");
}

use App\Services\SlugService;
use Core\Database;

// 4. Execution Limits
set_time_limit(0); 
ignore_user_abort(true);
ini_set('memory_limit', '1024M');

// Helper for Real-time Browser Output
function logProgress(string $msg, string $color = '#0f0'): void {
    echo "<div style='color:{$color}; font-family:monospace;'>[" . date('H:i:s') . "] {$msg}</div>";
    echo str_repeat(' ', 1024 * 64); // Force browser buffer flush
    if (ob_get_level() > 0) ob_flush();
    flush();
}

echo "<html><body style='background:#1a1a1a; color:#eee; padding:30px; line-height:1.6;'>";
echo "<h2>EMAG.PK Bulletproof Migration</h2><hr>";

$slugService = new SlugService();

try {
    $pdo = Database::getInstance();
    logProgress("✔ Database Connected.");

    // --- STEP 1: FRESH START (DATABASE) ---
    logProgress("⌛ Wiping existing products, categories, and orders...");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE seller_category_markups;");
    $pdo->exec("TRUNCATE TABLE order_items;");
    $pdo->exec("TRUNCATE TABLE orders;");
    $pdo->exec("TRUNCATE TABLE product_images;");
    $pdo->exec("TRUNCATE TABLE products;");
    $pdo->exec("TRUNCATE TABLE categories;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    logProgress("✔ Database cleaned.");

    // --- STEP 2: FRESH START (FILESYSTEM) ---
    $uploadDir = PUBLIC_PATH . '/uploads/products';
    if (is_dir($uploadDir)) {
        logProgress("⌛ Cleaning old product images...");
        foreach (glob($uploadDir . '/*') as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep' && basename($file) !== '.htaccess') {
                unlink($file);
            }
        }
        logProgress("✔ Filesystem cleaned.");
    } else {
        mkdir($uploadDir, 0755, true);
    }

} catch (Exception $e) {
    die("<div style='color:#f44; padding:15px; border:1px solid #f44;'><b>Fatal Error:</b> " . $e->getMessage() . "</div>");
}

// --- STEP 3: OPEN CSV ---
$csvFile = __DIR__ . '/csv-emag-file.csv';
if (!file_exists($csvFile)) {
    die("<div style='color:#f44; padding:15px;'><b>Error:</b> 'csv-emag-file.csv' not found in root.</div>");
}

$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle); 
if (!$headers) die("<b>Error:</b> CSV is empty or invalid.");
$headers = array_map('trim', $headers);

$productsCount = 0;
$categoriesCache = [];

/**
 * Securely downloads and saves product images
 */
function downloadAndSave($url) {
    $url = trim($url);
    if (empty($url)) return null;

    $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);
    $data = @file_get_contents($url, false, $ctx);
    if (!$data) return null;

    $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
    $name = bin2hex(random_bytes(10)) . '.' . $ext;
    $relPath = '/uploads/products/' . $name;
    
    file_put_contents(PUBLIC_PATH . $relPath, $data);
    return $relPath;
}

/**
 * Processes Category Strings (Parent > Child)
 */
function getCatId($str, &$cache, $pdo) {
    if (empty($str)) return null;
    $paths = explode(',', $str);
    $firstId = null;

    foreach ($paths as $path) {
        $parts = explode('>', $path);
        $parentId = null;
        foreach ($parts as $part) {
            $name = trim(strip_tags($part));
            if (empty($name)) continue;
            
            $key = ($parentId ?? '0') . '_' . strtolower($name);
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
                    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, parent_id) VALUES (?, ?, ?)");
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

logProgress("⌛ Starting Batch Import (Showing progress every 5 items)...", "#aaa");

while (($data = fgetcsv($handle)) !== false) {
    if (count($data) !== count($headers)) continue;
    $row = array_combine($headers, $data);
    
    $title = trim(strip_tags($row['Name'] ?? ''));
    if (empty($title)) continue;

    try {
        $pdo->beginTransaction();

        $sku   = !empty($row['SKU']) ? trim($row['SKU']) : 'WC-' . strtoupper(substr(md5($title), 0, 6));
        $desc  = trim($row['Description'] ?? $row['Short description'] ?? '');
        $price = (float)preg_replace('/[^0-9.]/', '', (string)($row['Sale price'] ?: $row['Regular price']));
        $stock = (trim((string)$row['In stock?']) === '1') ? (!empty($row['Stock']) ? (int)$row['Stock'] : 100) : 0;
        
        $catId = getCatId((string)$row['Categories'], $categoriesCache, $pdo);
        $slug  = $slugService->generate($title);

        $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, title, slug, description, base_price, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$catId, $sku, $title, $slug, $desc, $price, $stock]);
        $productId = (int)$pdo->lastInsertId();

        // Process Gallery Images
        $images = explode(',', (string)$row['Images']);
        foreach ($images as $idx => $url) {
            $path = downloadAndSave($url);
            if ($path) {
                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$productId, $path, ($idx === 0 ? 1 : 0), $idx]);
            }
        }

        $pdo->commit();
        $productsCount++;
        
        if ($productsCount % 5 === 0) {
            logProgress("✔ Imported {$productsCount} products...");
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        logProgress("✘ Error on '{$title}': " . $e->getMessage(), "#f44");
    }
}

fclose($handle);
logProgress("<hr><h2>✔ MIGRATION COMPLETE!</h2>", "#0f0");
logProgress("TOTAL PRODUCTS IMPORTED: <b>" . $productsCount . "</b>", "#fff");
echo "<br><a href='/admin/products' style='background:#0d6efd; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;'>View Dashboard</a>";
echo "</body></html>";
