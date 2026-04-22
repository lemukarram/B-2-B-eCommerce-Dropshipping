<?php
define('BASE_PATH', __DIR__ . '/src');
require_once __DIR__ . '/src/core/Database.php';
$config = require __DIR__ . '/config.php';

use Core\Database;

try {
    $pdo = Database::getInstance();
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Cleanup: Remove existing products and categories
    echo "Cleaning up existing data...\n";
    $pdo->exec("TRUNCATE TABLE order_items");
    $pdo->exec("TRUNCATE TABLE product_images");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("TRUNCATE TABLE seller_category_markups");
    $pdo->exec("TRUNCATE TABLE categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 2. Add buy_price to products
    echo "Adding 'buy_price' column to products...\n";
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS buy_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER pid");

    // 3. Create price_rules table
    echo "Creating 'price_rules' table...\n";
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

    echo "Database migration and cleanup completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
