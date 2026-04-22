<?php

declare(strict_types=1);

namespace App\Controllers\Store;

use App\Models\Order;
use App\Models\UserWallet;
use Core\Auth;
use Core\Request;
use Core\View;

class DashboardController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        
        // Simple stats for store dashboard
        $stats = [
            'total_orders'    => Order::query("SELECT COUNT(*) FROM orders WHERE user_id = ?", [$userId])->fetchColumn(),
            'pending_orders'  => Order::query("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'pending'", [$userId])->fetchColumn(),
            'delivered_orders'=> Order::query("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'delivered'", [$userId])->fetchColumn(),
            'wallet_balance'  => UserWallet::query("SELECT balance FROM user_wallets WHERE user_id = ?", [$userId])->fetchColumn() ?: '0.00',
        ];

        $recentOrders = Order::query(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$userId]
        )->fetchAll();

        View::render('store/dashboard', [
            'stats'         => $stats,
            'recentOrders'  => $recentOrders,
        ], 'store');
    }

    public function wallet(Request $request): void
    {
        $userId = Auth::id();
        $wallet = UserWallet::query("SELECT * FROM user_wallets WHERE user_id = ?", [$userId])->fetch();
        
        $transactions = UserWallet::query(
            "SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
            [$userId]
        )->fetchAll();

        View::render('store/wallet/index', [
            'wallet'       => $wallet,
            'transactions' => $transactions,
        ], 'store');
    }

    public function calculator(Request $request): void
    {
        View::render('store/calculator', [], 'store');
    }
}
