<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

/**
 * Requires an approved store session.
 */
class StoreMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        // Allow Admin, Seller, and Store roles
        if (!Auth::isStore() && !Auth::isSeller() && !Auth::isAdmin()) {
            Response::abort(403, 'Access denied.');
        }

        if (Auth::status() === 'suspended') {
            View::render('seller/suspended', [], 'store');
            exit;
        }
    }
}
