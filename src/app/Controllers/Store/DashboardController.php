<?php

declare(strict_types=1);

namespace App\Controllers\Store;

use App\Models\Order;
use App\Models\UserWallet;
use Core\Auth;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
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
        $page   = max(1, (int)$request->get('page', 1));

        $wallet       = UserWallet::findByUserId($userId);
        $transactions = UserWallet::transactions($userId, $page);
        
        $pdo     = Database::getInstance();
        $methods = $pdo->prepare(
            'SELECT * FROM seller_payment_methods WHERE user_id = ? ORDER BY is_primary DESC, id ASC'
        );
        $methods->execute([$userId]);

        $pendingRequests = $pdo->prepare(
            "SELECT pr.*, spm.method_type, spm.account_title, spm.account_number, spm.bank_name 
             FROM payment_requests pr
             JOIN seller_payment_methods spm ON spm.id = pr.payment_method_id
             WHERE pr.seller_id = ? AND pr.status = 'pending' 
             ORDER BY pr.created_at DESC"
        );
        $pendingRequests->execute([$userId]);

        View::render('store/wallet/index', [
            'wallet'          => $wallet,
            'transactions'    => $transactions['data'],
            'pagination'      => $transactions,
            'paymentMethods'  => $methods->fetchAll(),
            'pendingRequests' => $pendingRequests->fetchAll(),
            'success'         => Session::getFlash('success'),
            'errors'          => Session::errors(),
        ], 'store');
    }

    public function withdraw(Request $request): void
    {
        $userId = Auth::id();

        $v = new Validator($request->all(), [
            'amount'            => 'required|numeric|min:1',
            'payment_method_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/store/wallet');
        }

        $wallet = UserWallet::findByUserId($userId);
        $amount = number_format((float)$request->post('amount'), 2, '.', '');

        if (bccomp($wallet['balance'] ?? '0.00', $amount, 2) < 0) {
            Session::flashErrors(['amount' => ['Insufficient balance in your wallet.']]);
            Response::redirect('/store/wallet');
        }

        $pdo    = Database::getInstance();
        $method = $pdo->prepare(
            'SELECT id FROM seller_payment_methods WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $method->execute([$request->post('payment_method_id'), $userId]);

        if (!$method->fetch()) {
            Session::flashErrors(['payment_method_id' => ['Please select a valid payment method.']]);
            Response::redirect('/store/wallet');
        }

        $pdo->prepare(
            'INSERT INTO payment_requests (seller_id, payment_method_id, amount) VALUES (?, ?, ?)'
        )->execute([$userId, $request->post('payment_method_id'), $amount]);

        Session::flash('success', 'Your withdrawal request has been submitted to admin for processing.');
        Response::redirect('/store/wallet');
    }

    public function calculator(Request $request): void
    {
        View::render('store/calculator', [], 'store');
    }
}
