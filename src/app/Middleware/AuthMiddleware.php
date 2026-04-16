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
        if (!Auth::check()) {
            Response::redirect('/login');
        }
    }
}
