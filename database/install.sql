-- ============================================================
-- EMAG.PK Consolidated Database Schema & Seeds
-- MySQL 8.0 | InnoDB | utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `payment_requests`;
DROP TABLE IF EXISTS `wallet_transactions`;
DROP TABLE IF EXISTS `user_wallets`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `seller_category_markups`;
DROP TABLE IF EXISTS `seller_payment_methods`;
DROP TABLE IF EXISTS `seller_profiles`;
DROP TABLE IF EXISTS `users`;

-- 1. users
CREATE TABLE `users` (
    `id`             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`           VARCHAR(150)     NOT NULL,
    `email`          VARCHAR(255)     NOT NULL,
    `password`       VARCHAR(255)     NOT NULL,
    `role`           ENUM('admin','seller','store') NOT NULL DEFAULT 'store',
    `status`         ENUM('pending','approved','suspended') NOT NULL DEFAULT 'pending',
    `phone`          VARCHAR(20)      NULL,
    `remember_token` VARCHAR(100)     NULL,
    `parent_id`      INT UNSIGNED     NULL DEFAULT NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    INDEX `idx_users_role_status` (`role`, `status`),
    CONSTRAINT `fk_user_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. seller_profiles
CREATE TABLE `seller_profiles` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED NOT NULL,
    `business_name` VARCHAR(200) NOT NULL,
    `address`       TEXT         NULL,
    `city`          VARCHAR(100) NULL,
    `province`      VARCHAR(100) NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_seller_profiles_user` (`user_id`),
    CONSTRAINT `fk_sp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. seller_payment_methods
CREATE TABLE `seller_payment_methods` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED NOT NULL,
    `method_type`    ENUM('bank','easypaisa','jazzcash') NOT NULL,
    `account_title`  VARCHAR(200) NOT NULL,
    `account_number` VARCHAR(50)  NOT NULL,
    `bank_name`      VARCHAR(150) NULL,
    `is_primary`     TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_spm_user` (`user_id`),
    CONSTRAINT `fk_spm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. categories
CREATE TABLE `categories` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
    `name`        VARCHAR(150) NOT NULL,
    `slug`        VARCHAR(180) NOT NULL,
    `description` TEXT         NULL,
    `image`       VARCHAR(255) NULL,
    `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    INDEX `idx_categories_parent` (`parent_id`),
    INDEX `idx_categories_active_order` (`is_active`, `sort_order`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. seller_category_markups
CREATE TABLE `seller_category_markups` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `seller_id`    INT UNSIGNED    NOT NULL,
    `category_id`  INT UNSIGNED    NOT NULL,
    `markup_type`  ENUM('fixed', 'percent') NOT NULL DEFAULT 'percent',
    `markup_value` DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_seller_category` (`seller_id`, `category_id`),
    CONSTRAINT `fk_scm_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_scm_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. products
CREATE TABLE `products` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`    INT UNSIGNED    NULL,
    `sku`            VARCHAR(100)    NOT NULL,
    `title`          VARCHAR(255)    NOT NULL,
    `slug`           VARCHAR(300)    NOT NULL,
    `description`    TEXT            NULL,
    `base_price`     DECIMAL(10,2)   NOT NULL,
    `stock_quantity` INT             NOT NULL DEFAULT 0,
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_sku`  (`sku`),
    UNIQUE KEY `uq_products_slug` (`slug`),
    INDEX `idx_products_category` (`category_id`),
    INDEX `idx_products_active`   (`is_active`),
    CONSTRAINT `fk_prod_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. product_images
CREATE TABLE `product_images` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1)   NOT NULL DEFAULT 0,
    `sort_order` SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `idx_pi_product_primary` (`product_id`, `is_primary`),
    CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. orders
CREATE TABLE `orders` (
    `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`             INT UNSIGNED    NOT NULL,
    `parent_seller_id`    INT UNSIGNED    NULL,
    `order_number`        VARCHAR(30)     NOT NULL,
    `customer_name`       VARCHAR(150)    NOT NULL,
    `customer_phone`      VARCHAR(20)     NOT NULL,
    `customer_address`    TEXT            NOT NULL,
    `customer_city`       VARCHAR(100)    NOT NULL,
    `customer_province`   VARCHAR(100)    NOT NULL,
    `notes`               TEXT            NULL,
    `total_selling_price` DECIMAL(12,2)   NOT NULL,
    `total_base_price`    DECIMAL(12,2)   NOT NULL,
    `total_wholesale_price` DECIMAL(12,2) NOT NULL,
    `delivery_charge`     DECIMAL(10,2)   NOT NULL DEFAULT 200.00,
    `seller_profit`       DECIMAL(12,2)   NULL DEFAULT NULL,
    `store_profit`        DECIMAL(12,2)   NULL DEFAULT NULL,
    `failure_deduction`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `status`              ENUM('pending','processing','shipped','delivered','failed','returned','cancelled') NOT NULL DEFAULT 'pending',
    `profit_credited_at`  TIMESTAMP       NULL DEFAULT NULL,
    `created_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_orders_number` (`order_number`),
    INDEX `idx_orders_user`  (`user_id`),
    INDEX `idx_orders_parent` (`parent_seller_id`),
    INDEX `idx_orders_status`  (`status`),
    INDEX `idx_orders_created` (`created_at`),
    CONSTRAINT `fk_ord_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ord_parent` FOREIGN KEY (`parent_seller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. order_items
CREATE TABLE `order_items` (
    `id`                  INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `order_id`            INT UNSIGNED      NOT NULL,
    `product_id`          INT UNSIGNED      NULL,
    `product_title`       VARCHAR(255)      NOT NULL,
    `base_price_snapshot` DECIMAL(10,2)     NOT NULL,
    `wholesale_price_snapshot` DECIMAL(10,2) NOT NULL,
    `selling_price`       DECIMAL(10,2)     NOT NULL,
    `quantity`            SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    INDEX `idx_oi_order`   (`order_id`),
    INDEX `idx_oi_product` (`product_id`),
    CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. user_wallets
CREATE TABLE `user_wallets` (
    `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`         INT UNSIGNED   NOT NULL,
    `balance`         DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    `total_earned`    DECIMAL(14,2)  NOT NULL DEFAULT 0.00,
    `total_withdrawn` DECIMAL(14,2)  NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_uw_user` (`user_id`),
    CONSTRAINT `fk_uw_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. wallet_transactions
CREATE TABLE `wallet_transactions` (
    `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_id`        INT UNSIGNED   NOT NULL,
    `order_id`       INT UNSIGNED   NULL DEFAULT NULL,
    `type`           ENUM('credit','debit','withdrawal','penalty') NOT NULL,
    `amount`         DECIMAL(12,2)  NOT NULL,
    `balance_after`  DECIMAL(12,2)  NOT NULL,
    `description`    VARCHAR(255)   NOT NULL,
    `created_by`     INT UNSIGNED   NULL DEFAULT NULL,
    `created_at`     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_wt_user`    (`user_id`),
    INDEX `idx_wt_order`   (`order_id`),
    INDEX `idx_wt_type`    (`type`),
    INDEX `idx_wt_created` (`created_at`),
    CONSTRAINT `fk_wt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_wt_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_wt_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. payment_requests
CREATE TABLE `payment_requests` (
    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `seller_id`         INT UNSIGNED   NOT NULL,
    `payment_method_id` INT UNSIGNED   NOT NULL,
    `amount`            DECIMAL(12,2)  NOT NULL,
    `status`            ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
    `admin_note`        TEXT           NULL,
    `processed_at`      TIMESTAMP      NULL DEFAULT NULL,
    `created_at`        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_pr_seller` (`seller_id`),
    INDEX `idx_pr_status` (`status`),
    CONSTRAINT `fk_pr_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pr_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `seller_payment_methods` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. settings
CREATE TABLE `settings` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`         VARCHAR(100) NOT NULL,
    `value`       TEXT         NOT NULL,
    `description` VARCHAR(255) NULL,
    `updated_by`  INT UNSIGNED NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_settings_key` (`key`),
    CONSTRAINT `fk_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. login_attempts
CREATE TABLE `login_attempts` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_hash`      VARCHAR(64)  NOT NULL,
    `attempted_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_la_ip_time` (`ip_hash`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seeds
INSERT IGNORE INTO `settings` (`key`, `value`, `description`) VALUES
('default_delivery_charge', '200.00',  'Default delivery charge per order in PKR'),
('app_name',                'EMAG.PK', 'Application display name'),
('order_number_prefix',     'EMG',     'Prefix for generated order numbers'),
('max_bulk_upload_rows',    '500',     'Maximum rows allowed per bulk product CSV/XLSX upload'),
('seller_registration',     'open',    'open or closed — controls whether /register is accessible');

SET FOREIGN_KEY_CHECKS = 1;
