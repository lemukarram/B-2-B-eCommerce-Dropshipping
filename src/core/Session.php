<?php

declare(strict_types=1);

namespace Core;

/**
 * Thin wrapper around $_SESSION.
 * All session reads/writes go through this class — no superglobal access elsewhere.
 */
final class Session
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Store validation errors and old input for redirect-after-POST.
     */
    public static function flashErrors(array $errors): void
    {
        $_SESSION['_flash']['errors'] = $errors;
    }

    public static function flashOld(array $input): void
    {
        $_SESSION['_flash']['old'] = $input;
    }

    public static function errors(): array
    {
        $errors = $_SESSION['_flash']['errors'] ?? [];
        unset($_SESSION['_flash']['errors']);
        return $errors;
    }

    public static function old(string $key, mixed $default = ''): mixed
    {
        $value = $_SESSION['_flash']['old'][$key] ?? $default;
        // old input is consumed on first read
        if (isset($_SESSION['_flash']['old'][$key])) {
            unset($_SESSION['_flash']['old'][$key]);
        }
        return $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
