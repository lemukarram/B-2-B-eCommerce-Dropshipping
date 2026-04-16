<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Wallet model for both Sellers and Stores.
 */
class UserWallet extends BaseModel
{
    protected static string $table = 'user_wallets';

    public static function findByUserId(int $userId): ?array
    {
        $stmt = static::query(
            'SELECT * FROM user_wallets WHERE user_id = ? LIMIT 1',
            [$userId]
        );
        return $stmt->fetch() ?: null;
    }

    public static function getOrCreate(int $userId): array
    {
        $wallet = static::findByUserId($userId);
        if (!$wallet) {
            $id = static::insert([
                'user_id'         => $userId,
                'balance'         => 0.00,
                'total_earned'    => 0.00,
                'total_withdrawn' => 0.00,
            ]);
            return static::find($id);
        }
        return $wallet;
    }

    public static function transactions(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int) static::query(
            'SELECT COUNT(*) FROM wallet_transactions WHERE user_id = ?',
            [$userId]
        )->fetchColumn();

        $rows = static::query(
            'SELECT * FROM wallet_transactions WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$userId, $perPage, $offset]
        )->fetchAll();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    public static function addTransaction(int $userId, array $data): int
    {
        $pdo = \Core\Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Get current balance with lock
            $stmt = $pdo->prepare('SELECT balance FROM user_wallets WHERE user_id = ? FOR UPDATE');
            $stmt->execute([$userId]);
            $current = $stmt->fetchColumn();

            if ($current === false) {
                // Should already exist via getOrCreate, but as a fallback
                $current = 0.00;
                $pdo->prepare('INSERT INTO user_wallets (user_id, balance) VALUES (?, ?)')->execute([$userId, 0.00]);
            }

            $amount  = (float)$data['amount'];
            $newBal  = $data['type'] === 'credit' ? $current + $amount : $current - $amount;

            // Update wallet
            $updateSql = 'UPDATE user_wallets SET balance = ?';
            $params    = [$newBal];

            if ($data['type'] === 'credit') {
                $updateSql .= ', total_earned = total_earned + ?';
                $params[]   = $amount;
            } elseif ($data['type'] === 'withdrawal') {
                $updateSql .= ', total_withdrawn = total_withdrawn + ?';
                $params[]   = $amount;
            }

            $updateSql .= ' WHERE user_id = ?';
            $params[]   = $userId;

            $pdo->prepare($updateSql)->execute($params);

            // Record transaction
            $stmt = $pdo->prepare('
                INSERT INTO wallet_transactions 
                (user_id, order_id, type, amount, balance_after, description, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                $data['order_id'] ?? null,
                $data['type'],
                $amount,
                $newBal,
                $data['description'],
                $data['created_by'] ?? null
            ]);

            $id = (int)$pdo->lastInsertId();
            $pdo->commit();
            return $id;

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
