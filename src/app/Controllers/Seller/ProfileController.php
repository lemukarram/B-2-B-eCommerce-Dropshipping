<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\User;
use Core\Auth;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class ProfileController
{
    public function index(Request $request): void
    {
        $sellerId = Auth::id();
        $pdo      = Database::getInstance();

        $profile = $pdo->prepare(
            'SELECT u.*, sp.business_name, sp.address, sp.city, sp.province
             FROM users u
             LEFT JOIN seller_profiles sp ON sp.user_id = u.id
             WHERE u.id = ? LIMIT 1'
        );
        $profile->execute([$sellerId]);

        $methods = $pdo->prepare(
            'SELECT * FROM seller_payment_methods WHERE user_id = ? ORDER BY is_primary DESC, id ASC'
        );
        $methods->execute([$sellerId]);

        View::render('seller/profile/index', [
            'profile'        => $profile->fetch(),
            'paymentMethods' => $methods->fetchAll(),
            'errors'         => Session::errors(),
            'success'        => Session::getFlash('success'),
        ], 'seller');
    }

    public function update(Request $request): void
    {
        $sellerId = Auth::id();

        $v = new Validator($request->all(), [
            'name'          => 'required|max:150',
            'phone'         => 'required|regex:/^03[0-9]{9}$/',
            'business_name' => 'required|max:200',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/seller/profile');
        }

        $pdo = Database::getInstance();

        User::update($sellerId, [
            'name'  => trim($request->post('name')),
            'phone' => trim($request->post('phone')),
        ]);

        $pdo->prepare(
            'UPDATE seller_profiles SET business_name = ?, city = ?, province = ?, address = ?
             WHERE user_id = ?'
        )->execute([
            trim($request->post('business_name')),
            trim($request->post('city', '')),
            trim($request->post('province', '')),
            trim($request->post('address', '')),
            $sellerId,
        ]);

        Session::flash('success', 'Profile updated.');
        Response::redirect('/seller/profile');
    }

    public function storePaymentMethod(Request $request): void
    {
        $sellerId = Auth::id();

        $v = new Validator($request->all(), [
            'method_type'    => 'required|in:bank,easypaisa,jazzcash',
            'account_title'  => 'required|max:200',
            'account_number' => 'required|max:50',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/seller/profile');
        }

        $pdo = Database::getInstance();

        $pdo->prepare(
            'INSERT INTO seller_payment_methods
             (user_id, method_type, account_title, account_number, bank_name)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $sellerId,
            $request->post('method_type'),
            trim($request->post('account_title')),
            trim($request->post('account_number')),
            $request->post('method_type') === 'bank' ? trim($request->post('bank_name', '')) : null,
        ]);

        Session::flash('success', 'Payment method added.');
        Response::redirect('/seller/profile');
    }

    public function deletePaymentMethod(Request $request): void
    {
        $sellerId = Auth::id();
        $methodId = (int)$request->param('id');

        $pdo = Database::getInstance();
        $pdo->prepare(
            'DELETE FROM seller_payment_methods WHERE id = ? AND user_id = ?'
        )->execute([$methodId, $sellerId]);

        Session::flash('success', 'Payment method removed.');
        Response::redirect('/seller/profile');
    }

    public function setPrimaryPaymentMethod(Request $request): void
    {
        $sellerId = Auth::id();
        $methodId = (int)$request->param('id');

        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $pdo->prepare(
                'UPDATE seller_payment_methods SET is_primary = 0 WHERE user_id = ?'
            )->execute([$sellerId]);

            $pdo->prepare(
                'UPDATE seller_payment_methods SET is_primary = 1 WHERE id = ? AND user_id = ?'
            )->execute([$methodId, $sellerId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
        }

        Session::flash('success', 'Primary payment method updated.');
        Response::redirect('/seller/profile');
    }
}
