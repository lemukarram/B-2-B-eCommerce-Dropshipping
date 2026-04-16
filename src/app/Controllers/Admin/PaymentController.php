<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\WalletService;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

class PaymentController
{
    private WalletService $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    public function index(Request $request): void
    {
        $pdo    = Database::getInstance();
        $page   = max(1, (int)$request->get('page', 1));
        $status = $request->get('status', 'pending');
        $offset = ($page - 1) * 20;

        $status = in_array($status, ['pending','approved','rejected','paid'], true) ? $status : 'pending';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM payment_requests WHERE status = ?");
        $countStmt->execute([$status]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $pdo->prepare(
            "SELECT pr.*, u.name AS seller_name, u.email AS seller_email,
                    spm.method_type, spm.account_title, spm.account_number, spm.bank_name
             FROM payment_requests pr
             JOIN users u ON u.id = pr.seller_id
             JOIN seller_payment_methods spm ON spm.id = pr.payment_method_id
             WHERE pr.status = ?
             ORDER BY pr.created_at DESC
             LIMIT 20 OFFSET ?"
        );
        $stmt->execute([$status, $offset]);

        View::render('admin/payments/index', [
            'requests'   => $stmt->fetchAll(),
            'status'     => $status,
            'pagination' => ['total' => $total, 'per_page' => 20, 'current_page' => $page, 'last_page' => (int)ceil($total / 20)],
            'success'    => Session::getFlash('success'),
        ], 'admin');
    }

    public function process(Request $request): void
    {
        $pdo = Database::getInstance();
        $id  = (int)$request->param('id');

        $pr = $pdo->prepare('SELECT * FROM payment_requests WHERE id = ? LIMIT 1');
        $pr->execute([$id]);
        $paymentRequest = $pr->fetch();

        if (!$paymentRequest || $paymentRequest['status'] !== 'pending') {
            Response::abort(404, 'Payment request not found or already processed.');
        }

        $action = $request->post('action'); // 'approve', 'reject'

        if ($action === 'approve') {
            $this->walletService->processWithdrawal(
                (int)$paymentRequest['seller_id'],
                $paymentRequest['amount'],
                $id
            );
            Session::flash('success', 'Withdrawal processed.');
        } elseif ($action === 'reject') {
            $pdo->prepare(
                "UPDATE payment_requests SET status = 'rejected', admin_note = ?, processed_at = NOW() WHERE id = ?"
            )->execute([trim($request->post('admin_note', '')), $id]);
            Session::flash('success', 'Request rejected.');
        }

        Response::redirect('/admin/payments');
    }
}
