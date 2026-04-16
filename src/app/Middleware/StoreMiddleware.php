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

        if (!Auth::isStore()) {
            Response::abort(403, 'Access denied.');
        }

        if (Auth::status() === 'suspended') {
            View::render('seller/suspended', [], 'store');
            exit;
        }
    }
}
