<?php

declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $stmt = static::query(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findApprovedSellers(int $page = 1, int $perPage = 20): array
    {
        return static::paginate($page, $perPage, "role = 'seller' AND status = 'approved'");
    }

    public static function findPendingSellers(): array
    {
        return static::query(
            "SELECT * FROM users WHERE role = 'seller' AND status = 'pending' ORDER BY created_at DESC"
        )->fetchAll();
    }

    public static function findStoresBySeller(int $sellerId): array
    {
        return static::query(
            "SELECT * FROM users WHERE role = 'store' AND parent_id = ? ORDER BY created_at DESC",
            [$sellerId]
        )->fetchAll();
    }

    public static function createStore(array $data, int $sellerId): int
    {
        return static::insert([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'phone'     => $data['phone'] ?? null,
            'role'      => 'store',
            'status'    => 'approved', // Usually auto-approved if joined via seller link
            'parent_id' => $sellerId,
        ]);
    }

    public static function createSeller(array $data): int
    {
        return static::insert([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'phone'    => $data['phone'] ?? null,
            'role'     => 'store',
            'status'   => 'approved',
        ]);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function updatePassword(int $id, string $plain): void
    {
        static::update($id, [
            'password' => password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
    }
}
