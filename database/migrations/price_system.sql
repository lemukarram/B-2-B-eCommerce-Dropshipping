-- 1. Cleanup: Remove existing products and categories
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE order_items;
TRUNCATE TABLE product_images;
TRUNCATE TABLE products;
TRUNCATE TABLE seller_category_markups;
TRUNCATE TABLE categories;
SET FOREIGN_KEY_CHECKS = 1;

-- 2. Add buy_price to products
ALTER TABLE products ADD COLUMN `buy_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER pid;

-- 3. Create price_rules table
CREATE TABLE IF NOT EXISTS `price_rules` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
