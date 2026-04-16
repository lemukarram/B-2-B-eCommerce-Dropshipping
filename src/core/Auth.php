<?php

declare(strict_types=1);

namespace Core;

/**
 * Session-based authentication state.
 *
 * Handles login/logout and exposes role helpers.
 * Never touches the DB — that responsibility belongs to AuthService.
 */
final class Auth
{
    /**
     * Establish an authenticated session for the given user row.
     * Calls session_regenerate_id(true) to prevent session fixation.
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']     = (int) $user['id'];
        $_SESSION['user_role']   = $user['role'];
        $_SESSION['user_name']   = $user['name'];
        $_SESSION['user_parent_id'] = $user['parent_id'] ? (int) $user['parent_id'] : null;
        $_SESSION['user_status'] = $user['status'];
        $_SESSION['logged_in_at'] = time();
    }

    /**
     * Destroy the session completely.
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function parentId(): ?int
    {
        return isset($_SESSION['user_parent_id']) ? (int) $_SESSION['user_parent_id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function name(): ?string
    {
        return $_SESSION['user_name'] ?? null;
    }

    public static function status(): ?string
    {
        return $_SESSION['user_status'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isSeller(): bool
    {
        return self::role() === 'seller';
    }

    public static function isStore(): bool
    {
        return self::role() === 'store';
    }

    public static function isApprovedSeller(): bool
    {
        return self::isSeller() && self::status() === 'approved';
    }

    public static function isApprovedStore(): bool
    {
        return self::isStore() && self::status() === 'approved';
    }

    /**
     * Generate or retrieve the CSRF token for the current session.
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate a submitted CSRF token using timing-safe comparison.
     */
    public static function validateCsrf(string $token): bool
    {
        $stored = $_SESSION['csrf_token'] ?? '';
        return $stored !== '' && hash_equals($stored, $token);
    }
}
