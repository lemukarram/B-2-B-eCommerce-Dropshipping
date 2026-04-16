<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\Order;
use App\Models\UserWallet;
use Core\Auth;
use Core\Database;
use Core\Request;
use Core\View;

class DashboardController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        $pdo    = Database::getInstance();

        $wallet = UserWallet::findByUserId($userId);

        $stmt1 = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmt1->execute([$userId]);
        
        $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'pending'");
        $stmt2->execute([$userId]);

        $stmt3 = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'delivered'");
        $stmt3->execute([$userId]);

        $stats = [
            'total_orders'     => (int) $stmt1->fetchColumn(),
            'pending_orders'   => (int) $stmt2->fetchColumn(),
            'delivered_orders' => (int) $stmt3->fetchColumn(),
        ];

        $recentOrders = Order::forUser($userId, 1, 5)['data'];

        View::render('seller/dashboard', [
            'wallet'       => $wallet,
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
        ], 'seller');
    }
}
