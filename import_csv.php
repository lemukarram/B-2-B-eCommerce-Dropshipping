<?php

/**
 * EMAG.PK SUPER-SAFE MIGRATION SCRIPT
 * Designed to catch errors and prevent 500 timeouts.
 */

// 1. Force Error Reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// 2. Setup Paths
define('BASE_PATH',    __DIR__ . '/src');
define('CORE_PATH',    BASE_PATH . '/core');
define('PUBLIC_PATH',  __DIR__);

echo "<html><body style='font-family:monospace; background:#222; color:#0f0; padding:20px; line-height:1.5;'>";
echo "<h1>EMAG.PK Migration Debugger</h1><hr>";

// 3. Dependency Check
try {
    if (!file_exists(CORE_PATH . '/Autoloader.php')) {
        throw new Exception("CRITICAL: Autoloader.php not found at " . CORE_PATH);
    }
    require CORE_PATH . '/Autoloader.php';
    Autoloader::register();
    
    // Check if config exists
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception("CRITICAL: config.php is missing in the root.");
    }

    echo "✔ Core System Loaded.<br>";
} catch (Exception $e) {
    die("<span style='color:red;'>[ERROR] " . $e->getMessage() . "</span>");
}

use App\Services\SlugService;
use Core\Database;

// 4. Set Limits
set_time_limit(0); 
ini_set('memory_limit', '512M');

$slugService = new SlugService();

try {
    $pdo = Database::getInstance();
    echo "✔ Database Connected.<br>";

    // --- STEP 1: WIPE ---
    echo "⌛ Wiping database... ";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE product_images;");
    $pdo->exec("TRUNCATE TABLE products;");
    $pdo->exec("TRUNCATE TABLE categories;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "✔ Done.<br>";

} catch (Exception $e) {
    die("<span style='color:red;'>[DATABASE ERROR] " . $e->getMessage() . "</span>");
}

// --- STEP 2: OPEN CSV ---
$csvFile = __DIR__ . '/csv-emag-file.csv';
if (!file_exists($csvFile)) {
    die("<span style='color:red;'>[ERROR] csv-emag-file.csv not found in " . __DIR__ . "</span>");
}

$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle);
if (!$headers) die("Error: CSV Header is empty.");
$headers = array_map('trim', $headers);

$count = 0;
$categoriesCache = [];

/**
 * Robust Image Downloader with Timeout
 */
function safe_download($url) {
    $url = trim($url);
    if (empty($url)) return null;

    // Use a context to set a 5-second timeout to prevent 500 errors
    $ctx = stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true]
    ]);

    $data = @file_get_contents($url, false, $ctx);
    if (!$data) return null;

    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
    $name = bin2hex(random_bytes(10)) . '.' . $ext;
    $path = '/uploads/products/' . $name;
    $dest = PUBLIC_PATH . $path;

    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
    
    file_put_contents($dest, $data);
    return $path;
}

function get_cat_id($str, &$cache, $pdo) {
    if (empty($str)) return null;
    $paths = explode(',', $str);
    $finalId = null;

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
            if (!$finalId) $finalId = $parentId;
        }
    }
    return $finalId;
}

echo "⌛ Importing Products (this may take a few minutes)...<br>";
flush();

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
        $stock = (trim($row['In stock?']) === '1') ? (!empty($row['Stock']) ? (int)$row['Stock'] : 100) : 0;
        
        $catId = get_cat_id((string)$row['Categories'], $categoriesCache, $pdo);
        $slug  = $slugService->generate($title);

        $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, title, slug, description, base_price, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$catId, $sku, $title, $slug, $desc, $price, $stock]);
        $productId = (int)$pdo->lastInsertId();

        // Images
        $images = explode(',', (string)$row['Images']);
        foreach ($images as $idx => $url) {
            $local = safe_download($url);
            if ($local) {
                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$productId, $local, ($idx === 0 ? 1 : 0), $idx]);
            }
        }

        $pdo->commit();
        $count++;
        
        if ($count % 10 === 0) {
            echo "✔ Processed {$count} items...<br>";
            flush();
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "<span style='color:orange;'>! Error on '{$title}': " . $e->getMessage() . "</span><br>";
    }
}

fclose($handle);
echo "<hr><h2 style='color:#fff;'>✔ MIGRATION COMPLETE!</h2>";
echo "TOTAL PRODUCTS: " . $count . "<br>";
echo "<a href='/admin/products' style='color:#0f0;'>Go to Product List</a>";
echo "</body></html>";
