<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Services\AuthService;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(Request $request): void
    {
        View::render('auth/login', [
            'errors' => Session::errors(),
            'old'    => Session::getFlash('old', []),
        ], 'guest');
    }

    public function login(Request $request): void
    {
        // Rate limiting check
        $this->checkRateLimit($request->ip());

        $v = new Validator($request->all(), [
            'email'    => 'required|email|max:255',
            'password' => 'required|min:6',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->only(['email']));
            Response::redirectBack('/login');
        }

        $user = $this->authService->attempt(
            trim($request->post('email')),
            $request->post('password'),
            $request->post('remember') === 'on'
        );

        if ($user === null) {
            $this->recordFailedAttempt($request->ip());

            Session::flashErrors(['email' => ['Invalid credentials.']]);
            Session::flashOld($request->only(['email']));
            Response::redirectBack('/login');
        }

        // Redirect by role
        match ($user['role']) {
            'admin'  => Response::redirect('/admin'),
            'seller' => Response::redirect('/seller'),
            'store'  => Response::redirect('/store'),
            default  => Response::redirect('/'),
        };
    }

    public function showRegister(Request $request): void
    {
        if (Setting::get('seller_registration', 'open') !== 'open') {
            View::render('auth/registration_closed', [], 'guest');
            return;
        }

        View::render('auth/register', [
            'errors' => Session::errors(),
            'old'    => Session::getFlash('old', []),
        ], 'guest');
    }

    public function register(Request $request): void
    {
        if (Setting::get('seller_registration', 'open') !== 'open') {
            Response::abort(403, 'Registration is currently closed.');
        }

        // Clean phone for validation
        $phone = $request->post('phone', '');
        $cleanPhone = str_replace([' ', '-'], '', $phone);
        $requestData = $request->all();
        $requestData['phone'] = $cleanPhone;

        $phoneRegex = '/^((\+92)|(0092)|(92)|(0))?3[0-9]{9}$/';

        $v = new Validator($requestData, [
            'name'          => 'required|max:150',
            'email'         => 'required|email|max:255',
            'phone'         => 'required|regex:' . $phoneRegex,
            'business_name' => 'required|max:200',
            'password'      => 'required|min:8|confirmed',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->only(['name', 'email', 'phone', 'business_name']));
            Response::redirectBack('/register');
        }

        // Check for duplicate email
        if (User::findByEmail(trim($request->post('email'))) !== null) {
            Session::flashErrors(['email' => ['An account with this email already exists.']]);
            Session::flashOld($request->only(['name', 'email', 'phone', 'business_name']));
            Response::redirectBack('/register');
        }

        $this->authService->registerSeller([
            'name'          => trim($request->post('name')),
            'email'         => strtolower(trim($request->post('email'))),
            'phone'         => trim($request->post('phone')),
            'business_name' => trim($request->post('business_name')),
            'password'      => $request->post('password'),
        ]);

        Session::flash('success', 'Account created. Awaiting admin approval.');
        Response::redirect('/login');
    }

    public function showStoreRegister(Request $request): void
    {
        $sellerId = (int) $request->get('ref');
        if (!$sellerId) {
            Response::abort(404, 'Invalid referral link.');
        }

        View::render('auth/register_store', [
            'seller_id' => $sellerId,
            'errors'    => Session::errors(),
            'old'       => Session::getFlash('old', []),
        ], 'guest');
    }

    public function registerStore(Request $request): void
    {
        $v = new Validator($request->all(), [
            'seller_id'     => 'required|integer',
            'name'          => 'required|max:150',
            'email'         => 'required|email|max:255',
            'phone'         => 'required|regex:/^03[0-9]{9}$/',
            'password'      => 'required|min:8|confirmed',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->only(['name', 'email', 'phone']));
            Response::redirectBack('/register/store?ref=' . $request->post('seller_id'));
        }

        // Check for duplicate email
        if (User::findByEmail(trim($request->post('email'))) !== null) {
            Session::flashErrors(['email' => ['An account with this email already exists.']]);
            Session::flashOld($request->only(['name', 'email', 'phone']));
            Response::redirectBack('/register/store?ref=' . $request->post('seller_id'));
        }

        $this->authService->registerStore([
            'name'     => trim($request->post('name')),
            'email'    => strtolower(trim($request->post('email'))),
            'phone'    => trim($request->post('phone')),
            'password' => $request->post('password'),
        ], (int) $request->post('seller_id'));

        Session::flash('success', 'Store account created successfully. Please log in.');
        Response::redirect('/login');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Response::redirect('/login');
    }

    // ── Rate limiting helpers ──────────────────────────────────────────────

    private function checkRateLimit(string $ip): void
    {
        $hash = hash('sha256', $ip);
        $pdo  = \Core\Database::getInstance();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE ip_hash = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $stmt->execute([$hash]);
        $count = (int) $stmt->fetchColumn();

        if ($count >= 5) {
            Session::flashErrors(['email' => ['Too many login attempts. Please wait 15 minutes.']]);
            Response::redirect('/login');
        }
    }

    private function recordFailedAttempt(string $ip): void
    {
        $hash = hash('sha256', $ip);
        \Core\Database::getInstance()->prepare(
            'INSERT INTO login_attempts (ip_hash) VALUES (?)'
        )->execute([$hash]);

        // Prune old records (older than 1 hour)
        \Core\Database::getInstance()->exec(
            "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
    }
}
