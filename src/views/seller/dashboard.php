<?php $pageTitle = 'Dashboard Overview'; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary-subtle text-primary rounded-3 p-2 me-3">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                    <h6 class="card-subtitle text-muted mb-0">Total Orders</h6>
                </div>
                <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['total_orders']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning-subtle text-warning rounded-3 p-2 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <h6 class="card-subtitle text-muted mb-0">Pending</h6>
                </div>
                <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['pending_orders']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success-subtle text-success rounded-3 p-2 me-3">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                        <h6 class="card-subtitle text-muted mb-0">Delivered</h6>
                    </div>
                    <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['delivered_orders']) ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-25 rounded-3 p-2 me-3">
                        <i class="bi bi-wallet2 fs-4 text-white"></i>
                    </div>
                    <h6 class="card-subtitle text-white-50 mb-0">Balance</h6>
                </div>
                <h2 class="card-title fw-bold mb-0">Rs. <?= number_format($wallet['balance'] ?? 0, 2) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Store Orders</h5>
                <a href="/seller/orders" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Store</th>
                            <th>Profit</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td class="fw-medium">#<?= e($order['order_number']) ?></td>
                            <td><?= e($order['customer_name'] ?? 'Store') ?></td>
                            <td class="text-success fw-bold">Rs. <?= number_format($order['seller_profit'] ?? 0, 2) ?></td>
                            <td>
                                <?php 
                                    $statusClass = match($order['status']) {
                                        'pending' => 'bg-warning-subtle text-warning',
                                        'processing' => 'bg-info-subtle text-info',
                                        'delivered' => 'bg-success-subtle text-success',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                ?>
                                <span class="badge <?= $statusClass ?> border-0 rounded-pill px-3">
                                    <?= ucfirst(e($order['status'])) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                                <span class="text-muted">No orders recorded yet.</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="/seller/markups" class="btn btn-outline-primary text-start px-3 py-2">
                        <i class="bi bi-percent me-2"></i> Manage Markups
                    </a>
                    <a href="/seller/stores" class="btn btn-outline-primary text-start px-3 py-2">
                        <i class="bi bi-people me-2"></i> View My Stores
                    </a>
                    <a href="/seller/wallet" class="btn btn-outline-primary text-start px-3 py-2">
                        <i class="bi bi-wallet2 me-2"></i> Wallet History
                    </a>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm bg-dark text-white">
            <div class="card-body">
                <h6 class="fw-bold mb-2">Seller Support</h6>
                <p class="small text-white-50 mb-3">Need help with your account or orders? Contact our support team.</p>
                <a href="mailto:support@emag.pk" class="btn btn-sm btn-light w-100">Contact Support</a>
            </div>
        </div>
    </div>
</div>
