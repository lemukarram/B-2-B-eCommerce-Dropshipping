<?php $pageTitle = 'Seller: ' . e($seller['name']); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><?= e($seller['name']) ?></h4>
    <a href="/admin/sellers" class="btn btn-outline-secondary btn-sm rounded-pill">Back to Sellers</a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <!-- Seller Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="mb-0 fw-bold">Seller Information</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5">Business</dt>  <dd class="col-7 fw-bold"><?= e($seller['business_name'] ?? '—') ?></dd>
                    <dt class="col-5">Email</dt>     <dd class="col-7"><?= e($seller['email']) ?></dd>
                    <dt class="col-5">Phone</dt>     <dd class="col-7"><?= e($seller['phone'] ?? '—') ?></dd>
                    <dt class="col-5">Location</dt>  <dd class="col-7"><?= e($seller['city'] ?? '—') ?>, <?= e($seller['province'] ?? '—') ?></dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        <?php
                        $badges = ['approved' => 'success', 'pending' => 'warning', 'suspended' => 'danger'];
                        $badge  = $badges[$seller['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= e($seller['status']) ?></span>
                    </dd>
                    <dt class="col-5">Joined</dt>    <dd class="col-7"><?= date('d M Y', strtotime($seller['created_at'])) ?></dd>
                </dl>
            </div>
            <div class="card-footer bg-white border-0 p-3">
                <div class="d-grid gap-2">
                    <?php if ($seller['status'] !== 'approved'): ?>
                        <form method="POST" action="/admin/sellers/<?= $seller['id'] ?>/approve">
                            <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                            <button class="btn btn-sm btn-success w-100 rounded-pill">Approve Seller</button>
                        </form>
                    <?php endif; ?>
                    
                    <form method="POST" action="/admin/sellers/<?= $seller['id'] ?>/role" class="input-group input-group-sm">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <select name="role" class="form-select rounded-start-pill">
                            <option value="seller" <?= $seller['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                            <option value="store" <?= $seller['role'] === 'store' ? 'selected' : '' ?>>Store</option>
                        </select>
                        <button class="btn btn-primary rounded-end-pill px-3">Update Role</button>
                    </form>

                    <?php if ($seller['status'] !== 'suspended'): ?>
                        <form method="POST" action="/admin/sellers/<?= $seller['id'] ?>/suspend" onsubmit="return confirm('Suspend this seller?')">
                            <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                            <button class="btn btn-sm btn-outline-danger w-100 rounded-pill">Suspend Account</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Wallet Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Wallet & Balance</h6>
                <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#payoutModal">
                    Process Payout
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Available Balance</div>
                    <div class="fs-4 fw-bold text-primary">PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Total Earned</span>
                    <span class="fw-bold small">PKR <?= number_format($wallet['total_earned'] ?? 0, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Withdrawn</span>
                    <span class="fw-bold small">PKR <?= number_format($wallet['total_withdrawn'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 me-2" id="pills-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-orders" type="button" role="tab">Orders</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-ledger-tab" data-bs-toggle="pill" data-bs-target="#pills-ledger" type="button" role="tab">Wallet Ledger</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pills-orders" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0"><h6 class="mb-0 fw-bold">Recent Orders (Direct)</h6></div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th class="text-end">Customer Pays</th>
                                    <th class="text-end">Profit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="small fw-bold">#<?= e($o['order_number']) ?></td>
                                    <td class="small text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                                    <td class="text-end small">PKR <?= number_format($o['total_selling_price'] + $o['delivery_charge'], 0) ?></td>
                                    <td class="text-end text-success fw-bold small">
                                        <?= $o['seller_profit'] !== null ? 'PKR ' . number_format($o['seller_profit'], 0) : '—' ?>
                                    </td>
                                    <td><span class="badge bg-secondary small"><?= e($o['status']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($orders)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-5">No direct orders yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-ledger" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0"><h6 class="mb-0 fw-bold">Transaction History (Ledger)</h6></div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end pe-3">Balance After</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td class="ps-3 small"><?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?></td>
                                    <td>
                                        <?php 
                                        $badgeMap = ['credit' => 'bg-success-subtle text-success', 'debit' => 'bg-danger-subtle text-danger', 'withdrawal' => 'bg-primary-subtle text-primary', 'penalty' => 'bg-danger text-white']; 
                                        ?>
                                        <span class="badge rounded-pill xsmall <?= $badgeMap[$tx['type']] ?? 'bg-secondary' ?>">
                                            <?= ucfirst(e($tx['type'])) ?>
                                        </span>
                                    </td>
                                    <td class="small"><?= e($tx['description']) ?></td>
                                    <td class="text-end fw-bold small <?= in_array($tx['type'], ['credit']) ? 'text-success' : 'text-danger' ?>">
                                        <?= in_array($tx['type'], ['credit']) ? '+' : '-' ?>PKR <?= number_format($tx['amount'], 2) ?>
                                    </td>
                                    <td class="text-end pe-3 small">PKR <?= number_format($tx['balance_after'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($transactions)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-5 small">No transactions recorded yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payout Modal -->
<div class="modal fade" id="payoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Process Manual Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/admin/sellers/<?= $seller['id'] ?>/payout" method="POST">
                <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                <div class="modal-body">
                    <p class="small text-muted mb-4">Deduct amount from the wallet and record as withdrawal.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Amount (PKR)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="<?= (float)($wallet['balance'] ?? 0) ?>" required>
                        <div class="form-text">Available: PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Note</label>
                        <input type="text" name="description" class="form-control" placeholder="Manual payout">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Confirm Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>
