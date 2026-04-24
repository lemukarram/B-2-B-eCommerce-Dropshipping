<?php

declare(strict_types=1);

namespace App\Controllers\Store;

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
        $userId = Auth::id();
        $pdo    = Database::getInstance();

        $profile = $pdo->prepare(
            'SELECT u.*, sp.business_name, sp.address, sp.city, sp.province, sp.logo
             FROM users u
             LEFT JOIN seller_profiles sp ON sp.user_id = u.id
             WHERE u.id = ? LIMIT 1'
        );
        $profile->execute([$userId]);

        $methods = $pdo->prepare(
            'SELECT * FROM seller_payment_methods WHERE user_id = ? ORDER BY is_primary DESC, id ASC'
        );
        $methods->execute([$userId]);

        View::render('store/profile/index', [
            'profile'        => $profile->fetch(),
            'paymentMethods' => $methods->fetchAll(),
            'errors'         => Session::errors(),
            'success'        => Session::getFlash('success'),
        ], 'store');
    }

    public function update(Request $request): void
    {
        $userId = Auth::id();

        $v = new Validator($request->all(), [
            'name'          => 'required|max:150',
            'phone'         => 'required|regex:/^03[0-9]{9}$/',
            'business_name' => 'required|max:200',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/store/profile');
        }

        $pdo = Database::getInstance();

        // Update User basic info
        User::update($userId, [
            'name'  => trim($request->post('name')),
            'phone' => trim($request->post('phone')),
        ]);

        // Handle Logo Upload
        $logoPath = $request->post('existing_logo');
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            try {
                $fileService = new \App\Services\FileUploadService();
                $logoPath = $fileService->uploadImage($_FILES['logo'], 'users');
            } catch (\Exception $e) {
                Session::flashErrors(['logo' => [$e->getMessage()]]);
                Response::redirect('/store/profile');
            }
        }

        // Update or Create Seller Profile
        $stmt = $pdo->prepare(
            "INSERT INTO seller_profiles (user_id, business_name, address, city, province, logo)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE 
                business_name = VALUES(business_name),
                address       = VALUES(address),
                city          = VALUES(city),
                province      = VALUES(province),
                logo          = VALUES(logo)"
        );
        $stmt->execute([
            $userId,
            trim($request->post('business_name')),
            trim($request->post('address', '')),
            trim($request->post('city', '')),
            trim($request->post('province', '')),
            $logoPath
        ]);

        Session::flash('success', 'Profile updated successfully.');
        Response::redirect('/store/profile');
    }

    public function storePaymentMethod(Request $request): void
    {
        $userId = Auth::id();

        $v = new Validator($request->all(), [
            'method_type'    => 'required|in:bank,easypaisa,jazzcash',
            'account_title'  => 'required|max:200',
            'account_number' => 'required|max:50',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/store/profile');
        }

        $pdo = Database::getInstance();

        $pdo->prepare(
            'INSERT INTO seller_payment_methods
             (user_id, method_type, account_title, account_number, bank_name)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $userId,
            $request->post('method_type'),
            trim($request->post('account_title')),
            trim($request->post('account_number')),
            $request->post('method_type') === 'bank' ? trim($request->post('bank_name', '')) : null,
        ]);

        Session::flash('success', 'Payment method added.');
        Response::redirect('/store/profile');
    }

    public function deletePaymentMethod(Request $request): void
    {
        $userId = Auth::id();
        $methodId = (int)$request->param('id');

        $pdo = Database::getInstance();
        $pdo->prepare(
            'DELETE FROM seller_payment_methods WHERE id = ? AND user_id = ?'
        )->execute([$methodId, $userId]);

        Session::flash('success', 'Payment method removed.');
        Response::redirect('/store/profile');
    }
}
