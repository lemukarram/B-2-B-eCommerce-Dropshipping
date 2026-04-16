<?php

declare(strict_types=1);

namespace Core;

/**
 * HTTP Response helpers.
 */
class Response
{
    public static function redirect(string $url, int $status = 302): never
    {
        header('Location: ' . $url, true, $status);
        exit;
    }

    public static function redirectBack(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
        // Prevent open redirect: only allow relative or same-host redirects
        $host    = $_SERVER['HTTP_HOST'] ?? '';
        $parsed  = parse_url($ref);
        $refHost = $parsed['host'] ?? '';

        if ($refHost && $refHost !== $host) {
            self::redirect($fallback);
        }

        self::redirect($ref);
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        $title = $message ?: "HTTP $status";
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$safeTitle}</title>
    <style>
        body { font-family: system-ui, sans-serif; text-align: center; padding-top: 10%; background: #f8f9fa; color: #333; }
        h1 { font-size: 3rem; margin-bottom: 10px; }
        a { color: #0d6efd; text-decoration: none; }
    </style>
</head>
<body>
    <h1>{$status}</h1>
    <p>{$safeTitle}</p>
    <a href="/">Return Home</a>
</body>
</html>
HTML;
        exit;
    }

    public static function setStatus(int $code): void
    {
        http_response_code($code);
    }
}
