<?php $pageTitle = 'Payment Requests'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Payment Requests</h4>
</div>

<ul class="nav nav-tabs mb-3">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected'] as $s => $label): ?>
    <li class="nav-item">
        <a class="nav-link <?= ($status === $s) ? 'active' : '' ?>" href="/admin/payments?status=<?= $s ?>">
            <?= $label ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Seller</th>
                    <th>Method</th>
                    <th>Account</th>
                    <th class="text-end">Amount</th>
                    <th>Requested</th>
                    <?php if ($status === 'pending'): ?><th>Action</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td>
                        <div class="fw-medium"><?= e($r['seller_name']) ?></div>
                        <div class="text-muted small"><?= e($r['seller_email']) ?></div>
                    </td>
                    <td><span class="badge bg-secondary"><?= strtoupper(e($r['method_type'])) ?></span></td>
                    <td class="small">
                        <?php if ($r['bank_name']): ?>
                            <div><?= e($r['bank_name']) ?></div>
                        <?php endif; ?>
                        <div><?= e($r['account_title']) ?></div>
                        <div class="font-monospace"><?= e($r['account_number']) ?></div>
                    </td>
                    <td class="text-end fw-bold">PKR <?= number_format($r['amount'], 2) ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    <?php if ($status === 'pending'): ?>
                    <td>
                        <form method="POST" action="/admin/payments/<?= $r['id'] ?>/process" class="d-inline">
                            <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                            <input type="hidden" name="action" value="approve">
                            <button class="btn btn-sm btn-success" onclick="return confirm('Process this withdrawal?')">Pay</button>
                        </form>
                        <button class="btn btn-sm btn-outline-danger" type="button"
                                data-bs-toggle="modal" data-bs-target="#rejectModal<?= $r['id'] ?>">
                            Reject
                        </button>

                        <!-- Reject modal -->
                        <div class="modal fade" id="rejectModal<?= $r['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" action="/admin/payments/<?= $r['id'] ?>/process" class="modal-content">
                                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                    <input type="hidden" name="action" value="reject">
                                    <div class="modal-header"><h5 class="modal-title">Reject Request</h5></div>
                                    <div class="modal-body">
                                        <label class="form-label">Reason (optional)</label>
                                        <textarea name="admin_note" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$baseUrl    = '/admin/payments';
$queryExtra = '&status=' . urlencode($status);
include VIEW_PATH . '/components/pagination.php';
?>
