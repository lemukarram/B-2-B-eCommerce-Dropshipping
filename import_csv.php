<?php

declare(strict_types=1);

/**
 * EMAG.PK - DEFINITIVE PRODUCT IMPORT SCRIPT
 * Optimized for PHP 8.3+ and csv-emag-file.csv
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

// Increase limits for large imports
set_time_limit(0); 
ini_set('memory_limit', '1024M');

/**
 * Logging Helper
 */
function logMsg($msg, $color = '#0f0') {
    $timestamp = date('H:i:s');
    echo "<div style='color:{$color}; font-family:monospace; margin-bottom:4px; border-left: 3px solid {$color}; padding-left: 10px;'>";
    echo "<strong>[{$timestamp}]</strong> " . $msg; // Allowing HTML in logs for better formatting
    echo "</div>";
    
    // Force output buffer to flush
    echo str_repeat(' ', 1024 * 64);
    if (ob_get_level() > 0) ob_flush();
    flush();
}

/**
 * Image Downloader
 */
function downloadProductImage($url, $productTitle, $index = 0) {
    $url = trim($url);
    if (empty($url)) return null;

    try {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 15,
                'ignore_errors' => true,
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36\r\n"
            ]
        ]);
        
        $data = @file_get_contents($url, false, $ctx);
        if (!$data) return null;

        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
        if (empty($ext) || !in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $ext = 'jpg'; 
        }

        // Clean filename for SEO
        $cleanTitle = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $productTitle), '-'));
        $filename = $cleanTitle . '-' . ($index + 1) . '-' . bin2hex(random_bytes(2)) . '.' . $ext;
        
        $relativeDir = '/uploads/products/' . date('Y/m');
        $absoluteDir = PUBLIC_PATH . $relativeDir;
        
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $relativePath = $relativeDir . '/' . $filename;
        $absolutePath = PUBLIC_PATH . $relativePath;

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
            logMsg("✨ Created category: <span style='color:#fff'>$fullPathKey</span>", "#888");
        }
        $cache[$fullPathKey] = $parentId;
    }

    return $parentId;
}

// UI Start
echo "<html><head><title>EMAG.PK - Product Import v2</title>";
echo "<style>body{background:#0f0f0f; color:#d1d1d1; font-family: 'Consolas', monospace; padding:30px; line-height:1.4;} 
      h2{color:#00ff00; border-bottom: 1px solid #333; padding-bottom:10px;} .stats{background:#1a1a1a; padding:15px; border-radius:4px; border: 1px solid #333; margin-bottom:20px;}
      .btn{background:#222; color:#0f0; border: 1px solid #0f0; padding:8px 15px; text-decoration:none; display:inline-block; margin-top:10px;}
      .btn:hover{background:#0f0; color:#000;}</style></head><body>";
echo "<h2>🚀 EMAG.PK PRO IMPORTER v2.1</h2>";

try {
    $pdo = Database::getInstance();
    $slugService = new SlugService();

    if (isset($_GET['fresh']) && $_GET['fresh'] == '1') {
        logMsg("⚠️ PURGING DATABASE...", "red");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("TRUNCATE TABLE product_images; TRUNCATE TABLE products; TRUNCATE TABLE categories;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        logMsg("✅ Cleanup complete.", "yellow");
        echo "<script>setTimeout(function(){ window.location.href = 'import_csv.php'; }, 1000);</script>";
        exit;
    }

    $csvFile = __DIR__ . '/csv-emag-file.csv';
    if (!file_exists($csvFile)) die("<div style='color:red;'>Error: 'csv-emag-file.csv' not found.</div>");

    $handle = fopen($csvFile, 'r');
    // Explicitly providing all parameters to fgetcsv to avoid PHP 8.3 deprecation warnings
    $headers = fgetcsv($handle, 0, ",", "\"", "\\");
    if (!$headers) die("<div style='color:red;'>Error: Invalid CSV headers.</div>");
    
    $headers = array_map(function($h) {
        return trim(str_replace("\xEF\xBB\xBF", '', $h)); 
    }, $headers);

    $imported = 0; $skipped = 0; $errors = 0;
    $categoriesCache = [];
    $batchSize = 30; // Increased batch size
    $startFrom = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $currentRow = 0;

    logMsg("🔍 Scanning CSV. Starting from Row #" . ($startFrom + 1), "#00bcd4");

    while (($data = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
        $currentRow++;
        if ($currentRow <= $startFrom) continue;
        if (count($data) < count($headers)) continue;

        $row = array_combine($headers, array_slice($data, 0, count($headers)));
        
        $title = trim(strip_tags(html_entity_decode($row['Name'] ?? '', ENT_QUOTES, 'UTF-8')));
        if (empty($title)) continue;

        $sku = !empty($row['SKU']) ? trim($row['SKU']) : 'EP-' . strtoupper(substr(md5($title), 0, 8));

        // Duplicate Check with Detailed Logging
        $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = ? OR title = ? LIMIT 1");
        $stmt->execute([$sku, $title]);
        $existing = $stmt->fetch();
        if ($existing) {
            $skipped++;
            logMsg("⏩ Row {$currentRow} Skipped: Product already exists (SKU: $sku)", "#555");
            continue;
        }

        try {
            $pdo->beginTransaction();

            // Price Logic
            $regPrice = (float)preg_replace('/[^0-9.]/', '', (string)($row['Regular price'] ?? '0'));
            $salePrice = (float)preg_replace('/[^0-9.]/', '', (string)($row['Sale price'] ?? '0'));
            $basePrice = ($salePrice > 0) ? $salePrice : $regPrice;

            // Stock
            $stockQty = 0;
            if (($row['In stock?'] ?? '') == '1') {
                $stockQty = !empty($row['Stock']) ? (int)$row['Stock'] : 100;
            }

            $categoryId = getOrCreateCategoryId((string)($row['Categories'] ?? ''), $categoriesCache, $pdo);
            $slug = $slugService->generate($title);
            
            // Unique Slug Check
            $sCheck = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
            $sCheck->execute([$slug]);
            if ($sCheck->fetch()) $slug .= '-' . rand(10, 99);

            $desc = trim($row['Description'] ?? $row['Short description'] ?? '');

            $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, title, slug, description, base_price, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([$categoryId, $sku, $title, $slug, $desc, $basePrice, $stockQty]);
            $productId = (int)$pdo->lastInsertId();

            // Images - No strict limit as requested
            $imageUrls = explode(',', (string)($row['Images'] ?? ''));
            $imgCount = 0;
            foreach ($imageUrls as $url) {
                $path = downloadProductImage($url, $title, $imgCount);
                if ($path) {
                    $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)")
                        ->execute([$productId, $path, ($imgCount === 0 ? 1 : 0), $imgCount]);
                    $imgCount++;
                }
                if ($imgCount >= 10) break; // Reasonable limit for performance
            }

            $pdo->commit();
            $imported++;
            logMsg("✅ Row {$currentRow} Imported: <strong>" . substr($title, 0, 40) . "...</strong>", "#4CAF50");

            if ($imported >= $batchSize) {
                $next = $currentRow;
                logMsg("🔄 Batch Complete. Continuing from row $next...", "cyan");
                echo "<script>setTimeout(function(){ window.location.href = 'import_csv.php?start=$next'; }, 1000);</script>";
                fclose($handle);
                exit;
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors++;
            logMsg("❌ Error Row {$currentRow}: " . $e->getMessage(), "orange");
        }
    }

    fclose($handle);
    echo "<hr><div class='stats'><h3>🎉 IMPORT COMPLETED</h3>";
    echo "Imported: <strong>$imported</strong> | Skipped: <strong>$skipped</strong> | Errors: <strong>$errors</strong></div>";
    echo "<a href='/admin/products' class='btn'>Back to Dashboard</a> <a href='import_csv.php?fresh=1' class='btn' style='border-color:red; color:red;'>Wipe & Restart</a>";

} catch (Exception $e) {
    die("<div style='color:red;'>Fatal Error: " . $e->getMessage() . "</div>");
}
echo "</body></html>";
