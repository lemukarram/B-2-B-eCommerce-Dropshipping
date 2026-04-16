<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;
use Core\View;

/**
 * Requires an approved seller session.
 * Pending/suspended sellers get a specific message instead of full access.
 */
class SellerMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        if (!Auth::isSeller()) {
            Response::abort(403, 'Access denied.');
        }

        if (Auth::status() === 'pending') {
            // Show a "pending approval" page rather than a generic 403
            View::render('seller/pending', [], 'seller');
            exit;
        }

        if (Auth::status() === 'suspended') {
            View::render('seller/suspended', [], 'seller');
            exit;
        }
    }
}
