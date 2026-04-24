<?php $pageTitle = 'My Wallet'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Wallet & Earnings</h4>
    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#withdrawModal">
        <i class="bi bi-cash-stack me-2"></i> Withdraw Funds
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-bg-primary">
            <div class="card-body py-4">
                <div class="text-white-50 small text-uppercase fw-bold mb-1">Available Balance</div>
                <div class="fs-2 fw-bold text-white">PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body py-4 text-center">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Earned</div>
                <div class="fs-2 fw-bold text-dark">PKR <?= number_format($wallet['total_earned'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white text-center">
            <div class="card-body py-4">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Withdrawn</div>
                <div class="fs-2 fw-bold text-dark">PKR <?= number_format($wallet['total_withdrawn'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($pendingRequests)): ?>
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
    <div class="card-header bg-white py-3 border-0">
        <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-hourglass-split me-2"></i> Pending Withdrawal Requests</h6>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted">
                    <th class="ps-4">Date</th>
                    <th>Method</th>
                    <th>Account Info</th>
                    <th class="text-end pe-4">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRequests as $pr): ?>
                <tr>
                    <td class="ps-4 small"><?= date('d M Y', strtotime($pr['created_at'])) ?></td>
                    <td class="small text-capitalize"><?= e($pr['method_type']) ?></td>
                    <td class="small">
                        <div class="fw-bold"><?= e($pr['account_title']) ?></div>
                        <div class="text-muted"><?= e($pr['account_number']) ?> <?= $pr['bank_name'] ? '(' . e($pr['bank_name']) . ')' : '' ?></div>
                    </td>
                    <td class="text-end pe-4 fw-bold text-dark">PKR <?= number_format($pr['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Transaction History</h5>
                <?php if ($pagination['last_page'] > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for($i=1; $i<=$pagination['last_page']; $i++): ?>
                            <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">Date</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Description</th>
                            <th class="border-0 text-end">Amount</th>
                            <th class="border-0 text-end pe-4">Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td class="ps-4 small"><?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?></td>
                            <td>
                                <?php 
                                $badgeMap = [
                                    'credit'     => 'bg-success-subtle text-success', 
                                    'debit'      => 'bg-danger-subtle text-danger', 
                                    'withdrawal' => 'bg-primary-subtle text-primary', 
                                    'penalty'    => 'bg-danger text-white'
                                ]; 
                                ?>
                                <span class="badge rounded-pill xsmall <?= $badgeMap[$tx['type']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst(e($tx['type'])) ?>
                                </span>
                            </td>
                            <td class="small"><?= e($tx['description']) ?></td>
                            <td class="text-end fw-bold small <?= in_array($tx['type'], ['credit']) ? 'text-success' : 'text-danger' ?>">
                                <?= in_array($tx['type'], ['credit']) ? '+' : '-' ?>PKR <?= number_format($tx['amount'], 2) ?>
                            </td>
                            <td class="text-end pe-4 small">PKR <?= number_format($tx['balance_after'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-journal-text fs-1 mb-2 d-block opacity-25"></i>
                                No transactions recorded yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Funds Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Request Withdrawal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/store/wallet/withdraw" method="POST">
                <?= csrf_input() ?>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Amount to Withdraw (PKR)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">Rs.</span>
                            <input type="number" name="amount" class="form-control border-start-0 ps-0" placeholder="0.00" min="1" step="0.01" max="<?= (float)($wallet['balance'] ?? 0) ?>" required>
                        </div>
                        <div class="form-text small">Max available: <strong>PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></strong></div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">Select Payment Method</label>
                        <?php if (empty($paymentMethods)): ?>
                            <div class="alert alert-warning py-2 small mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> No payment methods found. 
                                <a href="/store/profile" class="alert-link">Add one in Profile & Bank.</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($paymentMethods as $index => $m): ?>
                            <div class="form-check p-3 border rounded-3 mb-2 hover-bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method_id" id="method<?= $m['id'] ?>" value="<?= $m['id'] ?>" <?= $index === 0 ? 'checked' : '' ?> required>
                                <label class="form-check-input-label d-block" for="method<?= $m['id'] ?>">
                                    <span class="d-block fw-bold text-dark text-capitalize"><?= e($m['method_type']) ?> <?= $m['bank_name'] ? '- ' . e($m['bank_name']) : '' ?></span>
                                    <span class="d-block small text-muted"><?= e($m['account_title']) ?> | <?= e($m['account_number']) ?></span>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" <?= empty($paymentMethods) ? 'disabled' : '' ?>>Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * FIX: Modal Backdrop issues.
 * Move the modal to document.body to escape the 'animate-fade-in' stacking context.
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('withdrawModal');
    if (modal) {
        document.body.appendChild(modal);
    }
});
</script>

<style>
.hover-bg-light:hover {
    background-color: #f8fafc;
    cursor: pointer;
}
</style>
