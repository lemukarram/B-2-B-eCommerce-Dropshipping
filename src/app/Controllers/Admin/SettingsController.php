<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Setting;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

class SettingsController
{
    public function index(Request $request): void
    {
        View::render('admin/settings/index', [
            'settings' => Setting::allAsArray(),
            'errors'   => Session::errors(),
            'success'  => Session::getFlash('success'),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $allowed = [
            'default_delivery_charge',
            'app_name',
            'order_number_prefix',
            'max_bulk_upload_rows',
            'seller_registration',
        ];

        foreach ($allowed as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                Setting::set($key, trim($value), Auth::id());
            }
        }

        Session::flash('success', 'Settings saved.');
        Response::redirect('/admin/settings');
    }
}
