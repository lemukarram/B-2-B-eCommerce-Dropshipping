<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

/**
 * Ensures the user is authenticated.
 * Redirects unauthenticated requests to /login.
 */
class AuthMiddleware
{
    public function handle(Request $request): void
    {
        // 1. If not in session, try to recover from cookie (Remember Me)
        if (!Auth::check()) {
            // Already handled in Core\Auth::check() which is called above
        }

        // 2. If STILL not logged in, redirect to login
        if (!Auth::check()) {
            Response::redirect('/login');
        }
    }
}
