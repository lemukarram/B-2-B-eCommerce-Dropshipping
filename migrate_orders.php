<?php
// Migration for enhanced order tracking
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__ . '/src');
require_once __DIR__ . '/src/core/Database.php';

use Core\Database;

try {
    $pdo = Database::getInstance();
    
    echo "<h3>Updating Database for Order Profits tracking</h3>";

    // Add total_buy_price to orders
    try {
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `total_buy_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `notes` ");
        echo "<span style='color: green;'>✔ Added `total_buy_price` to `orders`</span><br>";
    } catch (\PDOException $e) {
        echo "<span style='color: orange;'>~ `total_buy_price` already exists or error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }

    // Add buy_price_snapshot to order_items
    try {
        $pdo->exec("ALTER TABLE `order_items` ADD COLUMN `buy_price_snapshot` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `product_title` ");
        echo "<span style='color: green;'>✔ Added `buy_price_snapshot` to `order_items`</span><br>";
    } catch (\PDOException $e) {
        echo "<span style='color: orange;'>~ `buy_price_snapshot` already exists or error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }

    echo "<h2 style='color: green;'>✅ Migration Successful</h2>";
} catch (\Throwable $e) {
    echo "<h2 style='color: red;'>❌ Migration Failed</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
