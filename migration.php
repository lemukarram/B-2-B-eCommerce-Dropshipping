<?php
/**
 * EMAG.PK - Database Migration Utility
 * This script adds the necessary columns for the Store Logo and Ledger enhancements.
 */

declare(strict_types=1);

// Configuration - Ensure this matches your directory structure
define('BASE_PATH', __DIR__ . '/src');
require_once __DIR__ . '/src/core/Database.php';

use Core\Database;

echo "<html><body style='font-family: sans-serif; line-height: 1.6; padding: 20px; background: #f4f7f6;'>";
echo "<div style='max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 10px; shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
echo "<h2 style='color: #2563eb; border-bottom: 2px solid #eee; padding-bottom: 10px;'>🚀 EMAG.PK Database Migration</h2>";

try {
    $pdo = Database::getInstance();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<ul style='list-style: none; padding: 0;'>";

    // 1. Add logo column to seller_profiles
    echo "<li style='margin-bottom: 15px;'>";
    try {
        $pdo->exec("ALTER TABLE `seller_profiles` ADD COLUMN `logo` VARCHAR(255) NULL DEFAULT NULL AFTER `business_name` ");
        echo "<span style='color: green; font-weight: bold;'>✔ SUCCESS:</span> Added `logo` column to `seller_profiles`.";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column name')) {
            echo "<span style='color: #666;'>ℹ INFO:</span> `logo` column already exists in `seller_profiles`.";
        } else {
            throw $e;
        }
    }
    echo "</li>";

    // 2. Ensure seller_profiles exists for all current users (in case some were missing)
    echo "<li style='margin-bottom: 15px;'>";
    $pdo->exec("
        INSERT IGNORE INTO seller_profiles (user_id, business_name)
        SELECT id, name FROM users WHERE role IN ('seller', 'store')
    ");
    echo "<span style='color: green; font-weight: bold;'>✔ SUCCESS:</span> Verified profile entries for all Sellers and Stores.";
    echo "</li>";

    echo "</ul>";

    echo "<div style='margin-top: 30px; padding: 15px; background: #d1fae5; color: #065f46; border-radius: 6px;'>";
    echo "<strong>Migration Completed!</strong> All database changes have been applied successfully.";
    echo "</div>";

    echo "<p style='color: #dc2626; font-weight: bold; margin-top: 20px;'>⚠️ SECURITY WARNING: Please delete this file (`migration.php`) from your server immediately.</p>";
    echo "<a href='/admin' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a>";

} catch (\Throwable $e) {
    echo "<div style='margin-top: 30px; padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 6px; border-left: 5px solid #dc2626;'>";
    echo "<strong>❌ Migration Failed:</strong><br>";
    echo "<pre style='margin-top: 10px; white-space: pre-wrap; font-size: 0.85rem;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}

echo "</div></body></html>";
