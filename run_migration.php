<?php

// Ensure errors are displayed during the migration
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__ . '/src');
require_once __DIR__ . '/src/core/Database.php';

use Core\Database;

echo "<html><body style='font-family: sans-serif; padding: 20px;'>";
echo "<h2>Database Migration: CZ Import & Price Management</h2>";

try {
    $pdo = Database::getInstance();
    
    // 1. Cleanup old data
    echo "<h3>1. Cleaning up existing products and categories</h3>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = ['order_items', 'product_images', 'products', 'seller_category_markups', 'categories'];
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE `$table`");
        echo "<span style='color: green;'>✔ Truncated `$table`</span><br>";
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "<h3>2. Updating Table Structures</h3>";

    // 2a. categories.reference
    try {
        $pdo->exec("ALTER TABLE `categories` ADD COLUMN `reference` VARCHAR(50) NULL AFTER `id`");
        $pdo->exec("CREATE UNIQUE INDEX `uq_categories_reference` ON `categories`(`reference`)");
        echo "<span style='color: green;'>✔ Added `reference` to `categories` table</span><br>";
    } catch (\PDOException $e) {
        echo "<span style='color: orange;'>~ `reference` column in `categories` already exists or error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }

    // 2b. products.pid
    try {
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `pid` VARCHAR(50) NULL AFTER `id`");
        $pdo->exec("CREATE UNIQUE INDEX `uq_products_pid` ON `products`(`pid`)");
        echo "<span style='color: green;'>✔ Added `pid` to `products` table</span><br>";
    } catch (\PDOException $e) {
        echo "<span style='color: orange;'>~ `pid` column in `products` already exists or error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }

    // 2c. products.buy_price
    try {
        $pdo->exec("ALTER TABLE `products` ADD COLUMN `buy_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `pid`");
        echo "<span style='color: green;'>✔ Added `buy_price` to `products` table</span><br>";
    } catch (\PDOException $e) {
        echo "<span style='color: orange;'>~ `buy_price` column in `products` already exists or error: " . htmlspecialchars($e->getMessage()) . "</span><br>";
    }

    // 3. Create price_rules table
    echo "<h3>3. Creating Price Rules Table</h3>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `price_rules` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `rule_type`  ENUM('overall', 'category', 'range') NOT NULL,
        `category_id` INT UNSIGNED NULL,
        `min_price`  DECIMAL(10,2) NULL,
        `max_price`  DECIMAL(10,2) NULL,
        `margin_type` ENUM('fixed', 'percent', 'percent_cap') NOT NULL,
        `margin_value` DECIMAL(10,2) NOT NULL,
        `max_cap`    DECIMAL(10,2) NULL,
        `priority`   INT NOT NULL DEFAULT 0,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        CONSTRAINT `fk_pr_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "<span style='color: green;'>✔ `price_rules` table is ready</span><br>";

    echo "<h2 style='color: green; margin-top: 30px;'>✅ Migration Completed Successfully!</h2>";
    echo "<p style='color: red; font-weight: bold;'>Security Warning: For your protection, please delete this file (`run_migration.php`) from your server immediately.</p>";
    echo "<a href='/admin' style='display: inline-block; padding: 10px 15px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a>";

} catch (\Throwable $e) {
    echo "<h2 style='color: red;'>❌ Critical Error</h2>";
    echo "<pre style='background: #f8d7da; padding: 15px; border: 1px solid #f5c2c7; color: #842029;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

echo "</body></html>";
