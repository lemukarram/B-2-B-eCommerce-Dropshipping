<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

define('BASE_PATH',    __DIR__ . '/src');
define('APP_PATH',     BASE_PATH . '/app');
define('CORE_PATH',    BASE_PATH . '/core');

require CORE_PATH . '/Autoloader.php';
Autoloader::register();

use Core\Database;

try {
    $pdo = Database::getInstance();
    echo "<h2>Import Status</h2>";
    echo "Total Products: <strong>" . $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() . "</strong><br>";
    echo "Total Images: <strong>" . $pdo->query('SELECT COUNT(*) FROM product_images')->fetchColumn() . "</strong><br>";
    echo "Total Categories: <strong>" . $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() . "</strong><br>";
    
    echo "<h3>Last 5 Products</h3>";
    $last = $pdo->query('SELECT title, sku, base_price FROM products ORDER BY id DESC LIMIT 5')->fetchAll();
    if ($last) {
        foreach($last as $p) {
            echo "- " . htmlspecialchars($p['title']) . " (SKU: " . htmlspecialchars($p['sku']) . ") - " . $p['base_price'] . " PKR<br>";
        }
    } else {
        echo "No products found.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
// unlink(__FILE__); // Disabled unlinking for now to ensure we can see the result
