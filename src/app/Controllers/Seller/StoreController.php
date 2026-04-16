<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\User;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

class StoreController
{
    public function index(Request $request): void
    {
        $sellerId = Auth::id();
        $stores   = User::findStoresBySeller($sellerId);

        View::render('seller/stores/index', [
            'stores' => $stores,
        ], 'seller');
    }

    public function show(Request $request): void
    {
        $id    = (int) $request->param('id');
        $store = User::find($id);

        if (!$store || (int)$store['parent_id'] !== Auth::id()) {
            Response::abort(404, 'Store not found.');
        }

        View::render('seller/stores/show', [
            'store' => $store,
        ], 'seller');
    }
}
