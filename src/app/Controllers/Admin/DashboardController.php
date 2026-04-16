<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Models\User;
use Core\Database;
use Core\Request;
use Core\View;

class DashboardController
{
    public function index(Request $request): void
    {
        $pdo = Database::getInstance();

        $stats = [
            'total_orders'    => (int) $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'pending_orders'  => (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
            'pending_sellers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND status = 'pending'")->fetchColumn(),
            'total_sellers'   => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller' AND status = 'approved'")->fetchColumn(),
            'revenue_today'   => $pdo->query("SELECT COALESCE(SUM(total_selling_price),0) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
        ];

        $recentOrders = Order::adminList(1, 10)['data'];

        View::render('admin/dashboard', [
            'stats'        => $stats,
            'recentOrders' => $recentOrders,
        ], 'admin');
    }
}
