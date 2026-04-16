<?php

declare(strict_types=1);

/**
 * EMAG.PK - Main Entry Point
 * 
 * Optimized for cPanel / Shared Hosting (No Docker required).
 */

// ── Path constants ─────────────────────────────────────────────────────────
define('BASE_PATH',    __DIR__ . '/src');                    // Path to src/ folder
define('APP_PATH',     BASE_PATH . '/app');
define('CORE_PATH',    BASE_PATH . '/core');
define('VIEW_PATH',    BASE_PATH . '/views');
define('CONFIG_PATH',  BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('PUBLIC_PATH',  __DIR__);

// ── Load Configuration ───────────────────────────────────────────────────────
$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    die("Error: config.php is missing. Please create it or run install.php.");
}

// ── Autoloader ─────────────────────────────────────────────────────────────
require CORE_PATH . '/Autoloader.php';
Autoloader::register();

// ── Vendor (Composer — PhpSpreadsheet etc.) ────────────────────────────────
$vendor = __DIR__ . '/vendor/autoload.php';
if (is_file($vendor)) {
    require $vendor;
}

// ── Load configuration ─────────────────────────────────────────────────────
$appConfig = require CONFIG_PATH . '/app.php';

// Configure error display based on environment
if ($appConfig['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

date_default_timezone_set($appConfig['timezone']);

// Global exception handler
set_exception_handler(function (\Throwable $e) use ($appConfig) {
    if ($appConfig['debug']) {
        http_response_code(500);
        echo "<pre>" . e((string)$e) . "</pre>";
    } else {
        error_log((string)$e);
        \Core\Response::abort(500, 'Internal Server Error.');
    }
});

// ── Global XSS helper ─────────────────────────────────────────────────────
// Use e($value) in every view instead of raw echo.
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Session ────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// ── Bootstrap router and dispatch ─────────────────────────────────────────
$router  = new \Core\Router();
$request = new \Core\Request();

require BASE_PATH . '/routes.php';

$router->dispatch($request);
