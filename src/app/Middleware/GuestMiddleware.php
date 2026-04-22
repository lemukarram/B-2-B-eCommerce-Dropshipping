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
        if (Auth::check()) {
            match (Auth::role()) {
                'admin'  => Response::redirect('/admin'),
                'seller' => Response::redirect('/seller'),
                'store'  => Response::redirect('/store'),
                default  => Response::redirect('/'),
            };
        }
    }
}
