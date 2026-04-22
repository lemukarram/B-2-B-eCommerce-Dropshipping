<?php $pageTitle = 'Seller: ' . e($seller['name']); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><?= e($seller['name']) ?></h4>
    <a href="/admin/sellers" class="btn btn-outline-secondary btn-sm">Back to Sellers</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Seller Info</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5">Name</dt>     <dd class="col-7"><?= e($seller['name']) ?></dd>
                    <dt class="col-5">Email</dt>    <dd class="col-7"><?= e($seller['email']) ?></dd>
                    <dt class="col-5">Phone</dt>    <dd class="col-7"><?= e($seller['phone'] ?? '—') ?></dd>
                    <dt class="col-5">Business</dt> <dd class="col-7"><?= e($seller['business_name'] ?? '—') ?></dd>
                    <dt class="col-5">City</dt>     <dd class="col-7"><?= e($seller['city'] ?? '—') ?></dd>
                    <dt class="col-5">Province</dt> <dd class="col-7"><?= e($seller['province'] ?? '—') ?></dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        <?php
                        $badges = ['approved' => 'success', 'pending' => 'warning', 'suspended' => 'danger'];
                        $badge  = $badges[$seller['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= e($seller['status']) ?></span>
                    </dd>
                    <dt class="col-5">Role</dt>
                    <dd class="col-7">
                        <span class="badge bg-<?= $seller['role'] === 'seller' ? 'primary' : 'info' ?>"><?= ucfirst(e($seller['role'])) ?></span>
                    </dd>
                    <dt class="col-5">Joined</dt>   <dd class="col-7"><?= date('d M Y', strtotime($seller['created_at'])) ?></dd>
                </dl>
            </div>
            <div class="card-footer d-flex flex-column gap-2">
                <div>
                    <?php if ($seller['status'] !== 'approved'): ?>
                    <form method="POST" action="/admin/sellers/<?= $seller['id'] ?>/approve" class="d-inline">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <button class="btn btn-sm btn-success">Approve</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($seller['status'] !== 'suspended'): ?>
                    <form method="POST" action="/admin/sellers/<?= $seller['id'] ?>/suspend" class="d-inline"
                          onsubmit="return confirm('Suspend this user?')">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <button class="btn btn-sm btn-danger">Suspend</button>
                    </form>
                    <?php endif; ?>
                </div>
                
                <hr class="my-1">

                <form method="POST" action="/admin/sellers/<?= $seller['id'] ?>/role" class="d-flex gap-2 align-items-center">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                    <select name="role" class="form-select form-select-sm" style="width: auto;">
                        <option value="store" <?= $seller['role'] === 'store' ? 'selected' : '' ?>>Store</option>
                        <option value="seller" <?= $seller['role'] === 'seller' ? 'selected' : '' ?>>Seller</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Change Role</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Wallet</div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="text-muted small">Balance</div>
                    <div class="fs-5 fw-bold text-success">PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Total Earned</div>
                    <div class="fw-medium">PKR <?= number_format($wallet['total_earned'] ?? 0, 2) ?></div>
                </div>
                <div>
                    <div class="text-muted small">Total Withdrawn</div>
                    <div class="fw-medium">PKR <?= number_format($wallet['total_withdrawn'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Recent Orders</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Profit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><a href="/admin/orders/<?= $o['id'] ?>"><?= e($o['order_number']) ?></a></td>
                            <td><?= e($o['customer_name']) ?></td>
                            <td class="text-end">PKR <?= number_format($o['total_selling_price'], 0) ?></td>
                            <td class="text-end">
                                <?= $o['seller_profit'] !== null
                                    ? 'PKR ' . number_format($o['seller_profit'], 0)
                                    : '—' ?>
                            </td>
                            <td><span class="badge bg-secondary"><?= e($o['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
