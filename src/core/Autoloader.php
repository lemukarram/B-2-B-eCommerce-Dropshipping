<?php

declare(strict_types=1);

/**
 * PSR-4 style autoloader.
 * Namespace → directory mapping is defined in $map below.
 *
 * App\Controllers\Admin\ProductController
 *   → src/app/Controllers/Admin/ProductController.php
 *
 * Core\Database
 *   → src/core/Database.php
 */
final class Autoloader
{
    private static array $map = [];

    public static function register(): void
    {
        self::$map = [
            'Core\\' => CORE_PATH . DIRECTORY_SEPARATOR,
            'App\\'  => APP_PATH  . DIRECTORY_SEPARATOR,
        ];

        spl_autoload_register(function (string $class): void {
            foreach (self::$map as $prefix => $dir) {
                if (!str_starts_with($class, $prefix)) {
                    continue;
                }
                $relative = substr($class, strlen($prefix));
                $file     = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
                if (is_file($file)) {
                    require $file;
                }
                return;
            }
        });
    }
}
