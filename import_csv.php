<?php

declare(strict_types=1);

/**
 * EMAG.PK - DEFINITIVE PRODUCT IMPORT SCRIPT v3
 * Optimized for 200+ products, PHP 8.3+, and csv-emag-file.csv
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

// Define paths
define('BASE_PATH',    __DIR__ . '/src');
define('APP_PATH',     BASE_PATH . '/app');
define('CORE_PATH',    BASE_PATH . '/core');
define('CONFIG_PATH',  BASE_PATH . '/config');
define('PUBLIC_PATH',  __DIR__);

// Load Autoloader
require CORE_PATH . '/Autoloader.php';
Autoloader::register();

use App\Services\SlugService;
use Core\Database;

// Increase limits
set_time_limit(0); 
ini_set('memory_limit', '1024M');

/**
 * Logging Helper
 */
function logMsg($msg, $color = '#0f0') {
    $timestamp = date('H:i:s');
    echo "<div style='color:{$color}; font-family:monospace; margin-bottom:4px; border-left: 3px solid {$color}; padding-left: 10px;'>";
    echo "<strong>[{$timestamp}]</strong> " . $msg;
    echo "</div>";
    echo str_repeat(' ', 1024 * 64);
    if (ob_get_level() > 0) ob_flush();
    flush();
}

/**
 * Image Downloader with Cache check
 */
function downloadProductImage($url, $productTitle, $index = 0) {
    $url = trim($url);
    if (empty($url)) return null;

    try {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
        if (empty($ext) || !in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $ext = 'jpg'; 
        }

        $cleanTitle = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $productTitle), '-'));
        $filename = $cleanTitle . '-' . ($index + 1) . '.' . $ext;
        
        $relativeDir = '/uploads/products/' . date('Y/m');
        $absoluteDir = PUBLIC_PATH . $relativeDir;
        if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0755, true);

        $relativePath = $relativeDir . '/' . $filename;
        $absolutePath = PUBLIC_PATH . $relativePath;

        // Skip download if file already exists to save time
        if (file_exists($absolutePath)) return $relativePath;

        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
        $data = @file_get_contents($url, false, $ctx);
        if (!$data) return null;

        file_put_contents($absolutePath, $data);
        return $relativePath;

    } catch (Exception $e) {
        return null;
    }
}

/**
 * Category Processor
 */
function getOrCreateCategoryId($categoryString, &$cache, $pdo) {
    if (empty($categoryString)) return null;

    $categoryString = html_entity_decode($categoryString, ENT_QUOTES, 'UTF-8');
    $firstCategoryPath = explode(',', $categoryString)[0];
    $parts = explode('>', $firstCategoryPath);
    
    $parentId = null;
    $fullPathKey = '';

    foreach ($parts as $part) {
        $name = trim(strip_tags($part));
        if (empty($name)) continue;

        $fullPathKey .= ($fullPathKey ? ' > ' : '') . strtolower($name);

        if (isset($cache[$fullPathKey])) {
            $parentId = $cache[$fullPathKey];
            continue;
        }

        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND (parent_id = ? OR (? IS NULL AND parent_id IS NULL)) LIMIT 1");
        $stmt->execute([$name, $parentId, $parentId]);
        $result = $stmt->fetch();

        if ($result) {
            $parentId = (int)$result['id'];
        } else {
            $slugService = new SlugService();
            $slug = $slugService->generate($name);
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, parent_id, is_active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$name, $slug, $parentId]);
            $parentId = (int)$pdo->lastInsertId();
        }
        $cache[$fullPathKey] = $parentId;
    }
    return $parentId;
}

// UI Start
echo "<html><head><title>EMAG.PK - Definitive Import</title>";
echo "<style>body{background:#0a0a0a; color:#ccc; font-family: 'Segoe UI', monospace; padding:30px; line-height:1.5;} 
      h2{color:#00ff88; border-bottom: 2px solid #00ff88; display:inline-block; padding-bottom:5px;} .stats{background:#111; padding:15px; border-radius:4px; border: 1px solid #222; margin-bottom:20px;}
      .btn{background:#00ff88; color:#000; padding:10px 20px; text-decoration:none; font-weight:bold; border-radius:3px; display:inline-block; margin-top:10px;}
      .log-container{background:#000; padding:15px; border-radius:4px; border:1px solid #333; height: 500px; overflow-y: scroll; display: flex; flex-direction: column-reverse;}</style></head><body>";
echo "<h2>🚀 EMAG.PK BULK IMPORTER v3.0</h2>";

try {
    $pdo = Database::getInstance();
    $slugService = new SlugService();

    // 1. AUTO-PURGE IF STARTING FRESH
    $startFrom = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    if ($startFrom === 0 && (!isset($_GET['continue']))) {
        logMsg("🧹 AUTOMATIC PURGE: Removing old data for fresh import...", "yellow");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("TRUNCATE TABLE product_images;");
        $pdo->exec("TRUNCATE TABLE products;");
        $pdo->exec("TRUNCATE TABLE categories;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        logMsg("✅ Database is now clean.", "#00ff88");
    }

    $csvFile = __DIR__ . '/csv-emag-file.csv';
    if (!file_exists($csvFile)) die("<div style='color:red;'>Error: 'csv-emag-file.csv' not found.</div>");

    $handle = fopen($csvFile, 'r');
    $headers = fgetcsv($handle, 0, ",", "\"", "\\");
    if (!$headers) die("<div style='color:red;'>Error: CSV file is empty.</div>");
    
    $headers = array_map(function($h) { return trim(str_replace("\xEF\xBB\xBF", '', $h)); }, $headers);

    $imported = 0; $skipped = 0; $errors = 0;
    $categoriesCache = [];
    $batchSize = 40; // High performance batching
    $currentRow = 0;

    logMsg("🔍 Processing CSV from Row #" . ($startFrom + 1), "cyan");

    while (($data = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
        $currentRow++;
        if ($currentRow <= $startFrom) continue;
        if (count($data) < count($headers)) continue;

        $row = array_combine($headers, array_slice($data, 0, count($headers)));
        $title = trim(strip_tags(html_entity_decode($row['Name'] ?? '', ENT_QUOTES, 'UTF-8')));
        if (empty($title)) continue;

        $sku = !empty($row['SKU']) ? trim($row['SKU']) : 'EP-' . strtoupper(substr(md5($title), 0, 8));

        try {
            $pdo->beginTransaction();

            $regPrice = (float)preg_replace('/[^0-9.]/', '', (string)($row['Regular price'] ?? '0'));
            $salePrice = (float)preg_replace('/[^0-9.]/', '', (string)($row['Sale price'] ?? '0'));
            $basePrice = ($salePrice > 0) ? $salePrice : $regPrice;

            $stockQty = (($row['In stock?'] ?? '') == '1') ? (!empty($row['Stock']) ? (int)$row['Stock'] : 100) : 0;
            $categoryId = getOrCreateCategoryId((string)($row['Categories'] ?? ''), $categoriesCache, $pdo);
            
            $slug = $slugService->generate($title);
            $sCheck = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
            $sCheck->execute([$slug]);
            if ($sCheck->fetch()) $slug .= '-' . rand(100, 999);

            $desc = trim($row['Description'] ?? $row['Short description'] ?? '');

            $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, title, slug, description, base_price, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$categoryId, $sku, $title, $slug, $desc, $basePrice, $stockQty]);
            $productId = (int)$pdo->lastInsertId();

            // Images
            $imageUrls = explode(',', (string)($row['Images'] ?? ''));
            $imgCount = 0;
            foreach ($imageUrls as $url) {
                $path = downloadProductImage($url, $title, $imgCount);
                if ($path) {
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$productId, $path, ($imgCount === 0 ? 1 : 0), $imgCount]);
                    $imgCount++;
                }
                if ($imgCount >= 5) break; 
            }

            $pdo->commit();
            $imported++;
            logMsg("✅ [Row $currentRow] Imported: " . substr($title, 0, 45) . "...", "#00ff88");

            if ($imported >= $batchSize) {
                $next = $currentRow;
                logMsg("🔄 Batch Complete. Auto-redirecting to row $next...", "cyan");
                echo "<script>setTimeout(function(){ window.location.href = 'import_csv.php?start=$next&continue=1'; }, 500);</script>";
                fclose($handle);
                exit;
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors++;
            logMsg("❌ Error Row {$currentRow}: " . $e->getMessage(), "red");
        }
    }

    fclose($handle);
    echo "<hr><div class='stats'><h3>🎉 MISSION ACCOMPLISHED</h3>";
    echo "Total Products Processed: <strong>$currentRow</strong><br>";
    echo "Successfully Imported: <strong style='color:#00ff88;'>$imported</strong><br>";
    echo "Failed/Errors: <strong style='color:red;'>$errors</strong></div>";
    echo "<a href='/admin/products' class='btn'>VIEW PRODUCTS</a>";

} catch (Exception $e) {
    die("<div style='color:red;'>FATAL ERROR: " . $e->getMessage() . "</div>");
}
echo "</body></html>";
