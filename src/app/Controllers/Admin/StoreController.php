<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\UserWallet;
use App\Models\Order;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

class StoreController
{
    public function index(Request $request): void
    {
        $page   = max(1, (int)$request->get('page', 1));
        $status = in_array($request->get('status'), ['approved','pending','suspended'], true)
            ? $request->get('status')
            : 'approved';

        $pdo    = Database::getInstance();
        $offset = ($page - 1) * 20;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'store' AND status = ?");
        $countStmt->execute([$status]);
        $total = (int) $countStmt->fetchColumn();

        $stores = $pdo->prepare(
            "SELECT u.*, p.name as parent_seller_name
             FROM users u
             LEFT JOIN users p ON p.id = u.parent_id
             WHERE u.role = 'store' AND u.status = ?
             ORDER BY u.created_at DESC
             LIMIT 20 OFFSET ?"
        );
        $stores->execute([$status, $offset]);

        View::render('admin/stores/index', [
            'stores'     => $stores->fetchAll(),
            'status'     => $status,
            'pagination' => ['total' => $total, 'per_page' => 20, 'current_page' => $page, 'last_page' => (int)ceil($total / 20)],
        ], 'admin');
    }

    public function show(Request $request): void
    {
        $pdo = Database::getInstance();
        $id  = (int)$request->param('id');

        $stmt = $pdo->prepare(
            "SELECT u.*, p.name as parent_seller_name, p.email as parent_seller_email
             FROM users u
             LEFT JOIN users p ON p.id = u.parent_id
             WHERE u.id = ? AND u.role = 'store' LIMIT 1"
        );
        $stmt->execute([$id]);
        $store = $stmt->fetch();

        if (!$store) {
            Response::abort(404, 'Store not found.');
        }

        $wallet       = UserWallet::findByUserId($id);
        $ordersResult = Order::forUser($id, 1, 10);

        View::render('admin/stores/show', [
            'store'   => $store,
            'wallet'  => $wallet,
            'orders'  => $ordersResult['data'],
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function approve(Request $request): void
    {
        $id = (int)$request->param('id');
        User::update($id, ['status' => 'approved']);
        Session::flash('success', 'Store approved.');
        Response::redirect('/admin/stores/' . $id);
    }

    public function suspend(Request $request): void
    {
        $id = (int)$request->param('id');
        User::update($id, ['status' => 'suspended']);
        Session::flash('success', 'Store suspended.');
        Response::redirect('/admin/stores/' . $id);
    }
}
