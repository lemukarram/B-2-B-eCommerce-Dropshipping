<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;

/**
 * Redirects already-authenticated users away from guest-only routes
 * (login, register) to their respective dashboards.
 */
class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            return;
        }

        if (Auth::isAdmin()) {
            Response::redirect('/admin');
        }

        if (Auth::isApprovedSeller()) {
            Response::redirect('/seller');
        }

        // Pending/suspended sellers can still see the login page
        // (they need to know their status)
    }
}
