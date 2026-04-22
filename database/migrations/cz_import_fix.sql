-- Migration: Add CZ Import Fields
-- Run this in your MySQL console or PHPMyAdmin

ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `reference` VARCHAR(50) NULL AFTER `id`;
CREATE UNIQUE INDEX IF NOT EXISTS `uq_categories_reference` ON `categories`(`reference`);

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `pid` VARCHAR(50) NULL AFTER `id`;
CREATE UNIQUE INDEX IF NOT EXISTS `uq_products_pid` ON `products`(`pid`);
