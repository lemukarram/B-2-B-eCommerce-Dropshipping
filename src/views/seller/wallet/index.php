<?php $pageTitle = 'My Wallet'; ?>
<h4 class="mb-4">Wallet</h4>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-success">
            <div class="card-body">
                <div class="small">Available Balance</div>
                <div class="fs-3 fw-bold">PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-info text-white">
            <div class="card-body">
                <div class="small">Total Earned</div>
                <div class="fs-3 fw-bold">PKR <?= number_format($wallet['total_earned'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-secondary">
            <div class="card-body">
                <div class="small">Total Withdrawn</div>
                <div class="fs-3 fw-bold">PKR <?= number_format($wallet['total_withdrawn'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <!-- Withdrawal Form -->
        <div class="card">
            <div class="card-header">Request Withdrawal</div>
            <div class="card-body">
                <?php if (empty($paymentMethods)): ?>
                    <p class="text-muted">Add a payment method in <a href="/seller/profile">Profile</a> first.</p>
                <?php else: ?>
                    <form method="POST" action="/seller/wallet/withdraw">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <div class="mb-3">
                            <label class="form-label">Amount (PKR)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1"
                                   max="<?= e($wallet['balance'] ?? 0) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pay to</label>
                            <select name="payment_method_id" class="form-select" required>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?= $method['id'] ?>">
                                        <?= e(strtoupper($method['method_type'])) ?> — <?= e($method['account_title']) ?> (<?= e($method['account_number']) ?>)
                                        <?= $method['is_primary'] ? '(Primary)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Submit Request</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Requests -->
        <?php if (!empty($pendingRequests)): ?>
        <div class="card mt-3">
            <div class="card-header">Pending Requests</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($pendingRequests as $pr): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>PKR <?= number_format($pr['amount'], 2) ?></span>
                        <span class="badge bg-warning text-dark">Pending</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <!-- Transaction History -->
        <div class="card">
            <div class="card-header">Transaction History</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($tx['created_at'])) ?></td>
                            <td>
                                <?php $badgeMap = ['credit' => 'success', 'debit' => 'danger', 'withdrawal' => 'primary', 'penalty' => 'danger']; ?>
                                <span class="badge bg-<?= $badgeMap[$tx['type']] ?? 'secondary' ?>"><?= e($tx['type']) ?></span>
                            </td>
                            <td><?= e($tx['description']) ?></td>
                            <td class="text-end <?= in_array($tx['type'], ['credit']) ? 'text-success' : 'text-danger' ?>">
                                <?= in_array($tx['type'], ['credit']) ? '+' : '-' ?>PKR <?= number_format($tx['amount'], 2) ?>
                            </td>
                            <td class="text-end">PKR <?= number_format($tx['balance_after'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">No transactions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include VIEW_PATH . '/components/pagination.php'; // requires $pagination, $baseUrl ?>
    </div>
</div>
