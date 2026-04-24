<?php

declare(strict_types=1);

namespace App\Services;

use Core\Database;
use RuntimeException;

class WalletService
{
    /**
     * Credit profit to both Store and Seller (if applicable) when an order is delivered.
     */
    public function creditOrderProfit(int $orderId, ?int $adminId = null): void
    {
        $pdo = Database::getInstance();

        $order = $pdo->query(
            "SELECT * FROM orders WHERE id = {$orderId} LIMIT 1"
        )->fetch();

        if (!$order) {
            throw new RuntimeException("Order #{$orderId} not found.");
        }

        if ($order['profit_credited_at'] !== null) {
            throw new RuntimeException("Profit already credited for order #{$orderId}.");
        }

        $userId         = (int) $order['user_id'];
        $parentSellerId = $order['parent_seller_id'] ? (int) $order['parent_seller_id'] : null;

        $storeProfit  = '0.00';
        $sellerProfit = '0.00';

        if ($parentSellerId) {
            // Store Order: Split profit
            $storeProfit = bcsub($order['total_selling_price'], $order['total_wholesale_price'], 2);
            $sellerProfit = bcsub($order['total_wholesale_price'], $order['total_base_price'], 2);
        } else {
            // Direct Seller Order: All profit to seller
            $sellerProfit = bcsub($order['total_selling_price'], $order['total_base_price'], 2);
        }

        // Clamp to zero
        if (bccomp($storeProfit,  '0.00', 2) < 0) $storeProfit  = '0.00';
        if (bccomp($sellerProfit, '0.00', 2) < 0) $sellerProfit = '0.00';

        $pdo->beginTransaction();

        try {
            // Update order record
            $pdo->prepare(
                'UPDATE orders
                 SET status = ?, store_profit = ?, seller_profit = ?, profit_credited_at = NOW()
                 WHERE id = ?'
            )->execute(['delivered', $storeProfit, $sellerProfit, $orderId]);

            // Credit Store (user_id)
            if (bccomp($storeProfit, '0.00', 2) > 0) {
                $this->creditWallet($userId, $storeProfit, "Profit from order #{$order['order_number']}", $orderId, $adminId);
            }

            // Credit Seller (parent_seller_id OR user_id)
            $targetSellerId = $parentSellerId ?: $userId;
            if (bccomp($sellerProfit, '0.00', 2) > 0) {
                $this->creditWallet($targetSellerId, $sellerProfit, "Wholesale profit from order #{$order['order_number']}", $orderId, $adminId);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function creditWallet(int $userId, string $amount, string $description, int $orderId, ?int $adminId): void
    {
        $pdo = Database::getInstance();
        
        $wallet = $pdo->query(
            "SELECT * FROM user_wallets WHERE user_id = {$userId} FOR UPDATE"
        )->fetch();

        if (!$wallet) {
            throw new RuntimeException("Wallet not found for user #{$userId}.");
        }

        $newBalance     = bcadd($wallet['balance'],      $amount, 2);
        $newTotalEarned = bcadd($wallet['total_earned'], $amount, 2);

        $pdo->prepare(
            'UPDATE user_wallets SET balance = ?, total_earned = ? WHERE user_id = ?'
        )->execute([$newBalance, $newTotalEarned, $userId]);

        $pdo->prepare(
            'INSERT INTO wallet_transactions
             (user_id, order_id, type, amount, balance_after, description, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([$userId, $orderId, 'credit', $amount, $newBalance, $description, $adminId]);
    }

    /**
     * Deduct failure penalty. Penalty is ALWAYS deducted from the user who placed the order.
     */
    public function applyFailureDeduction(int $orderId, string $deduction, ?int $adminId = null): void
    {
        $pdo = Database::getInstance();

        $order = $pdo->query(
            "SELECT * FROM orders WHERE id = {$orderId} LIMIT 1"
        )->fetch();

        if (!$order) {
            throw new RuntimeException("Order #{$orderId} not found.");
        }

        $userId = (int) $order['user_id'];

        $pdo->beginTransaction();

        try {
            $pdo->prepare(
                'UPDATE orders SET status = ?, failure_deduction = ? WHERE id = ?'
            )->execute(['failed', $deduction, $orderId]);

            $wallet = $pdo->query(
                "SELECT * FROM user_wallets WHERE user_id = {$userId} FOR UPDATE"
            )->fetch();

            if (!$wallet) {
                throw new RuntimeException("Wallet not found for user #{$userId}.");
            }

            $newBalance = bcsub($wallet['balance'], $deduction, 2);
            if (bccomp($newBalance, '0.00', 2) < 0) {
                $newBalance = '0.00';
            }

            $pdo->prepare(
                'UPDATE user_wallets SET balance = ? WHERE user_id = ?'
            )->execute([$newBalance, $userId]);

            $pdo->prepare(
                'INSERT INTO wallet_transactions
                 (user_id, order_id, type, amount, balance_after, description, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $userId,
                $orderId,
                'penalty',
                $deduction,
                $newBalance,
                "Delivery failure deduction — order #{$order['order_number']}",
                $adminId,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Process a withdrawal request.
     */
    public function processWithdrawal(int $userId, string $amount, int $paymentRequestId, ?int $adminId = null): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $wallet = $pdo->query(
                "SELECT * FROM user_wallets WHERE user_id = {$userId} FOR UPDATE"
            )->fetch();

            if (bccomp($wallet['balance'], $amount, 2) < 0) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $newBalance      = bcsub($wallet['balance'],        $amount, 2);
            $newTotalWithdrawn = bcadd($wallet['total_withdrawn'], $amount, 2);

            $pdo->prepare(
                'UPDATE user_wallets SET balance = ?, total_withdrawn = ? WHERE user_id = ?'
            )->execute([$newBalance, $newTotalWithdrawn, $userId]);

            $pdo->prepare(
                'INSERT INTO wallet_transactions
                 (user_id, order_id, type, amount, balance_after, description, created_by)
                 VALUES (?, NULL, ?, ?, ?, ?, ?)'
            )->execute([
                $userId,
                'withdrawal',
                $amount,
                $newBalance,
                'Withdrawal processed',
                $adminId,
            ]);

            $pdo->prepare(
                'UPDATE payment_requests SET status = ?, processed_at = NOW() WHERE id = ?'
            )->execute(['paid', $paymentRequestId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
