<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Services\WalletService;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class OrderController
{
    private WalletService $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    public function index(Request $request): void
    {
        $page   = max(1, (int)$request->get('page', 1));
        $status = $request->get('status') ?: null;

        $result = Order::adminList($page, 20, $status);

        View::render('admin/orders/index', [
            'orders'     => $result['data'],
            'pagination' => $result,
            'status'     => $status,
        ], 'admin');
    }

    public function show(Request $request): void
    {
        $order = Order::withUser((int)$request->param('id'));
        if (!$order) {
            Response::abort(404, 'Order not found.');
        }

        $items = Order::itemsFor($order['id']);

        View::render('admin/orders/show', [
            'order' => $order,
            'items' => $items,
        ], 'admin');
    }

    public function updateStatus(Request $request): void
    {
        $order = Order::findOrFail((int)$request->param('id'));

        $v = new Validator($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,failed,returned,cancelled',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/admin/orders/' . $order['id']);
        }

        $newStatus = $request->post('status');
        $adminId   = Auth::id();

        if ($newStatus === 'delivered' && $order['status'] !== 'delivered') {
            $this->walletService->creditOrderProfit($order['id'], $adminId);
        } elseif (in_array($newStatus, ['failed', 'returned'], true) && $order['status'] !== $newStatus) {
            $deduction = $request->post('failure_deduction', '0.00');
            if ((float)$deduction > 0) {
                $this->walletService->applyFailureDeduction($order['id'], $deduction, $adminId);
            } else {
                Order::update($order['id'], ['status' => $newStatus]);
            }
        } else {
            Order::update($order['id'], ['status' => $newStatus]);
        }

        Session::flash('success', 'Order status updated to ' . $newStatus . '.');
        Response::redirect('/admin/orders/' . $order['id']);
    }
}
