<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

/**
 * Requires authenticated admin role.
 * Sends non-admins to their appropriate dashboard or login.
 */
class AdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        if (!Auth::isAdmin()) {
            Response::abort(403, 'Access denied.');
        }
    }
}
