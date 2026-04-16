<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;

/**
 * CSRF token validation for all POST requests.
 *
 * Token is read from either:
 *   $_POST['_csrf_token']         — form submissions
 *   X-CSRF-Token request header  — AJAX requests
 *
 * Uses hash_equals() for timing-safe comparison.
 */
class CsrfMiddleware
{
    public function handle(Request $request): void
    {
        if (!$request->isPost()) {
            return;
        }

        $token = $request->post('_csrf_token')
            ?? $request->header('X-CSRF-Token')
            ?? '';

        if (!Auth::validateCsrf($token)) {
            // Log the attempt
            error_log(sprintf(
                'CSRF validation failed: IP=%s URI=%s',
                $request->ip(),
                $request->uri()
            ));

            if ($request->isAjax()) {
                Response::json(['error' => 'CSRF token mismatch.'], 419);
            }

            Response::abort(419, 'Page expired. Please go back and try again.');
        }
    }
}
