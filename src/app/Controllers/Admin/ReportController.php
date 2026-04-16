<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Database;
use Core\Request;
use Core\View;

class ReportController
{
    public function index(Request $request): void
    {
        View::render('admin/reports/index', [], 'admin');
    }

    public function orders(Request $request): void
    {
        $pdo       = Database::getInstance();
        $dateFrom  = $request->get('date_from', date('Y-m-01'));
        $dateTo    = $request->get('date_to',   date('Y-m-d'));

        $summary = $pdo->prepare(
            "SELECT
                COUNT(*)                                AS total_orders,
                SUM(total_selling_price)                AS total_revenue,
                SUM(total_base_price)                   AS total_cost,
                SUM(seller_profit)                      AS total_seller_payouts,
                SUM(delivery_charge)                    AS total_delivery,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered,
                SUM(CASE WHEN status = 'failed'    THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN status = 'returned'  THEN 1 ELSE 0 END) AS returned
             FROM orders
             WHERE DATE(created_at) BETWEEN ? AND ?"
        );
        $summary->execute([$dateFrom, $dateTo]);

        View::render('admin/reports/orders', [
            'summary'  => $summary->fetch(),
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ], 'admin');
    }

    public function sellers(Request $request): void
    {
        $pdo = Database::getInstance();

        $rows = $pdo->query(
            "SELECT u.id, u.name, u.email, sp.business_name,
                    sw.balance, sw.total_earned, sw.total_withdrawn,
                    COUNT(o.id) AS total_orders,
                    SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders
             FROM users u
             LEFT JOIN seller_profiles sp ON sp.user_id = u.id
             LEFT JOIN user_wallets sw    ON sw.user_id = u.id
             LEFT JOIN orders o           ON o.user_id = u.id
             WHERE u.role = 'seller' AND u.status = 'approved'
             GROUP BY u.id
             ORDER BY sw.total_earned DESC"
        )->fetchAll();

        View::render('admin/reports/sellers', [
            'sellers' => $rows,
        ], 'admin');
    }
}
