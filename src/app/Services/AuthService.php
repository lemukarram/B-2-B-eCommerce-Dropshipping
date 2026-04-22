<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserWallet;
use Core\Auth;
use Core\Database;

class AuthService
{
    /**
     * Attempt login. Returns user array on success, null on failure.
     * Also rehashes passwords if cost factor changed.
     */
    public function attempt(string $email, string $password, bool $remember = false): ?array
    {
        $user = User::findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!User::verifyPassword($password, $user['password'])) {
            return null;
        }

        if (($user['role'] === 'seller' || $user['role'] === 'store') && $user['status'] === 'suspended') {
            return null;
        }

        // Rehash if bcrypt cost factor was updated
        if (User::needsRehash($user['password'])) {
            User::updatePassword($user['id'], $password);
        }

        Auth::login($user, $remember);
        return $user;
    }

    /**
     * Register a new seller account and create their wallet row.
     * Returns the new user ID.
     */
    public function registerSeller(array $data): int
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $userId = User::createSeller($data);

            UserWallet::getOrCreate($userId);

            // Create seller_profile with business_name
            $pdo->prepare(
                'INSERT INTO seller_profiles (user_id, business_name, city, province)
                 VALUES (?, ?, ?, ?)'
            )->execute([
                $userId,
                $data['business_name'] ?? '',
                $data['city']          ?? null,
                $data['province']      ?? null,
            ]);

            $pdo->commit();
            return $userId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Register a new Store (Referral) account under a Seller.
     */
    public function registerStore(array $data, int $sellerId): int
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $userId = User::createStore($data, $sellerId);

            UserWallet::getOrCreate($userId);

            $pdo->commit();
            return $userId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
