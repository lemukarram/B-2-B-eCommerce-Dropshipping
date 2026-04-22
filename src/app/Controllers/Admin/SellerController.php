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

class SellerController
{
    public function index(Request $request): void
    {
        $page   = max(1, (int)$request->get('page', 1));
        $status = in_array($request->get('status'), ['pending','approved','suspended'], true)
            ? $request->get('status')
            : 'approved';

        $pdo    = Database::getInstance();
        $offset = ($page - 1) * 20;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role IN ('seller', 'store') AND status = ?");
        $countStmt->execute([$status]);
        $total = (int) $countStmt->fetchColumn();

        $sellers = $pdo->prepare(
            "SELECT u.*, sp.business_name, sp.city
             FROM users u
             LEFT JOIN seller_profiles sp ON sp.user_id = u.id
             WHERE u.role IN ('seller', 'store') AND u.status = ?
             ORDER BY u.created_at DESC
             LIMIT 20 OFFSET ?"
        );
        $sellers->execute([$status, $offset]);

        View::render('admin/sellers/index', [
            'sellers'    => $sellers->fetchAll(),
            'status'     => $status,
            'pagination' => ['total' => $total, 'per_page' => 20, 'current_page' => $page, 'last_page' => (int)ceil($total / 20)],
        ], 'admin');
    }

    public function show(Request $request): void
    {
        $pdo = Database::getInstance();
        $id  = (int)$request->param('id');

        $stmt = $pdo->prepare(
            "SELECT u.*, sp.business_name, sp.address, sp.city, sp.province
             FROM users u
             LEFT JOIN seller_profiles sp ON sp.user_id = u.id
             WHERE u.id = ? AND u.role IN ('seller', 'store') LIMIT 1"
        );
        $stmt->execute([$id]);
        $seller = $stmt->fetch();

        if (!$seller) {
            Response::abort(404, 'User not found.');
        }

        $wallet       = UserWallet::findByUserId($id);
        $ordersResult = Order::forUser($id, 1, 10);

        View::render('admin/sellers/show', [
            'seller'  => $seller,
            'wallet'  => $wallet,
            'orders'  => $ordersResult['data'],
            'success' => \Core\Session::getFlash('success'),
        ], 'admin');
    }

    public function updateRole(Request $request): void
    {
        $id = (int)$request->param('id');
        $role = $request->post('role');

        if (in_array($role, ['seller', 'store'])) {
            User::update($id, ['role' => $role]);
            Session::flash('success', 'User role updated successfully.');
        } else {
            Session::flashErrors(['role' => ['Invalid role selected.']]);
        }

        Response::redirect('/admin/sellers/' . $id);
    }

    public function approve(Request $request): void
    {
        $id = (int)$request->param('id');
        User::update($id, ['status' => 'approved']);
        Session::flash('success', 'Seller approved.');
        Response::redirect('/admin/sellers/' . $id);
    }

    public function suspend(Request $request): void
    {
        $id = (int)$request->param('id');
        User::update($id, ['status' => 'suspended']);
        Session::flash('success', 'Seller suspended.');
        Response::redirect('/admin/sellers/' . $id);
    }
}
