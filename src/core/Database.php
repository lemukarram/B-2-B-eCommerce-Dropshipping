<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PDO singleton.
 *
 * Usage:
 *   $pdo  = Database::getInstance();
 *   $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
 *   $stmt->execute([$id]);
 *
 * ATTR_EMULATE_PREPARES = false forces real server-side prepared
 * statements — never emulated, never injectable via type-juggling.
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require BASE_PATH . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $cfg['host'],
                $cfg['port'],
                $cfg['name']
            );

            try {
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                // Never expose connection details in output
                error_log('DB connection failed: ' . $e->getMessage());
                throw new RuntimeException('Database connection failed.');
            }
        }

        return self::$instance;
    }

    // Prevent instantiation, cloning, and unserialization
    private function __construct() {}
    private function __clone() {}
    public function __wakeup(): never
    {
        throw new RuntimeException('Cannot unserialize a singleton.');
    }
}
