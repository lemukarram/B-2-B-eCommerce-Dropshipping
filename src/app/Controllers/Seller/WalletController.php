<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\UserWallet;
use Core\Auth;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class WalletController
{
    public function index(Request $request): void
    {
        $userId = Auth::id();
        $page   = max(1, (int)$request->get('page', 1));

        $wallet       = UserWallet::findByUserId($userId);
        $transactions = UserWallet::transactions($userId, $page);

        // Fetch seller's payment methods for withdrawal form
        $pdo     = Database::getInstance();
        $methods = $pdo->prepare(
            'SELECT * FROM seller_payment_methods WHERE user_id = ? ORDER BY is_primary DESC, id ASC'
        );
        $methods->execute([$userId]);

        // Pending withdrawal requests
        $pendingRequests = $pdo->prepare(
            "SELECT * FROM payment_requests WHERE seller_id = ? AND status = 'pending' ORDER BY created_at DESC"
        );
        $pendingRequests->execute([$userId]);

        View::render('seller/wallet/index', [
            'wallet'          => $wallet,
            'transactions'    => $transactions['data'],
            'pagination'      => $transactions,
            'paymentMethods'  => $methods->fetchAll(),
            'pendingRequests' => $pendingRequests->fetchAll(),
            'errors'          => Session::errors(),
            'success'         => Session::getFlash('success'),
        ], 'seller');
    }

    public function withdraw(Request $request): void
    {
        $userId = Auth::id();

        $v = new Validator($request->all(), [
            'amount'            => 'required|decimal|min:1',
            'payment_method_id' => 'required|integer',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/seller/wallet');
        }

        $wallet = UserWallet::findByUserId($userId);
        $amount = number_format((float)$request->post('amount'), 2, '.', '');

        if (bccomp($wallet['balance'] ?? '0.00', $amount, 2) < 0) {
            Session::flashErrors(['amount' => ['Insufficient balance.']]);
            Response::redirect('/seller/wallet');
        }

        // Verify payment method belongs to this seller
        $pdo    = Database::getInstance();
        $method = $pdo->prepare(
            'SELECT * FROM seller_payment_methods WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $method->execute([$request->post('payment_method_id'), $userId]);

        if (!$method->fetch()) {
            Session::flashErrors(['payment_method_id' => ['Invalid payment method.']]);
            Response::redirect('/seller/wallet');
        }

        // Create withdrawal request (admin processes it)
        $pdo->prepare(
            'INSERT INTO payment_requests (seller_id, payment_method_id, amount) VALUES (?, ?, ?)'
        )->execute([$userId, $request->post('payment_method_id'), $amount]);

        Session::flash('success', 'Withdrawal request submitted. Admin will process it shortly.');
        Response::redirect('/seller/wallet');
    }
}
