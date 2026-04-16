<?php $pageTitle = 'Store Dashboard'; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <div class="bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-cart-check fs-4"></i>
                </div>
                <h6 class="text-muted mb-2">Total Orders</h6>
                <h3 class="fw-bold mb-0"><?= number_format((int)$stats['total_orders']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 text-center py-4">
            <div class="card-body text-center py-4">
                <div class="bg-warning-subtle text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <h6 class="text-muted mb-2">Pending</h6>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format((int)$stats['pending_orders']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 text-center py-4">
            <div class="card-body text-center py-4">
                <div class="bg-success-subtle text-success rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-check2-circle fs-4"></i>
                </div>
                <h6 class="text-muted mb-2">Delivered</h6>
                <h3 class="fw-bold mb-0 text-success"><?= number_format((int)$stats['delivered_orders']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-primary text-white">
            <div class="card-body text-center py-4">
                <div class="bg-white bg-opacity-25 rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                    <i class="bi bi-wallet2 fs-4 text-white"></i>
                </div>
                <h6 class="text-white-50 mb-2">Wallet Balance</h6>
                <h3 class="fw-bold mb-0">Rs. <?= number_format((float)$stats['wallet_balance'], 2) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Orders</h5>
                <a href="/store/orders" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Profit</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td class="fw-medium">#<?= e($order['order_number']) ?></td>
                            <td><?= e($order['customer_name']) ?></td>
                            <td class="text-success fw-bold">Rs. <?= number_format((float)$order['store_profit'], 2) ?></td>
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
                                <span class="text-muted">No orders yet. <a href="/store/products">Start selling</a>.</span>
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
            <div class="card-body p-4 text-center">
                <div class="bg-primary-subtle text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 72px; height: 72px;">
                    <i class="bi bi-box-seam fs-2"></i>
                </div>
                <h5 class="fw-bold">Ready to Sell?</h5>
                <p class="text-muted mb-4">Browse our catalog and start adding orders for your customers to earn profit.</p>
                <a href="/store/products" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-shop-window me-2"></i> Browse Catalog
                </a>
            </div>
        </div>
        <div class="card border-0 shadow-sm bg-dark text-white">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-2">Store Support</h6>
                <p class="small text-white-50 mb-0">Need help with an order or have questions about products? Contact your parent seller or our support team.</p>
            </div>
        </div>
    </div>
</div>
