<?php $pageTitle = 'Store Dashboard'; ?>

<div class="row mb-4">
    <div class="col-12 animate-fade-in">
        <div class="card border-0 bg-gradient-primary text-white overflow-hidden shadow-lg" style="border-radius: 1.5rem;">
            <div class="card-body p-4 p-lg-5 position-relative">
                <div class="position-relative z-index-1">
                    <h2 class="fw-bold mb-2">Welcome back, <?= e(\Core\Auth::name()) ?>!</h2>
                    <p class="lead opacity-75 mb-0">"Empowering Your Business, One Order at a Time."</p>
                </div>
                <div class="position-absolute top-0 end-0 h-100 p-5 opacity-10 d-none d-lg-block">
                    <i class="bi bi-rocket-takeoff" style="font-size: 10rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 animate-fade-in" style="animation-delay: 0.1s;">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary-light rounded-3 p-2 me-3">
                        <i class="bi bi-cart-check text-primary fs-4"></i>
                    </div>
                    <span class="text-muted small fw-bold text-uppercase">Total Orders</span>
                </div>
                <h3 class="fw-bold mb-0"><?= number_format((int)$stats['total_orders']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning-light rounded-3 p-2 me-3">
                        <i class="bi bi-clock-history text-warning fs-4"></i>
                    </div>
                    <span class="text-muted small fw-bold text-uppercase">Pending</span>
                </div>
                <h3 class="fw-bold mb-0"><?= number_format((int)$stats['pending_orders']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success-light rounded-3 p-2 me-3">
                        <i class="bi bi-check2-circle text-success fs-4"></i>
                    </div>
                    <span class="text-muted small fw-bold text-uppercase">Delivered</span>
                </div>
                <h3 class="fw-bold mb-0"><?= number_format((int)$stats['delivered_orders']) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-secondary text-white">
            <div class="card-body py-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-25 rounded-3 p-2 me-3">
                        <i class="bi bi-wallet2 text-white fs-4"></i>
                    </div>
                    <span class="text-white text-opacity-75 small fw-bold text-uppercase">Wallet Balance</span>
                </div>
                <h3 class="fw-bold mb-0">Rs. <?= number_format((float)$stats['wallet_balance'], 2) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 animate-fade-in" style="animation-delay: 0.2s;">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Activity</h5>
                <a href="/store/orders" class="btn btn-sm btn-link text-decoration-none fw-bold">View All Activity</a>
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
                                <span class="text-muted">No activity yet. <a href="/store/products">"Scale Beyond Borders with EMAG.PK"</a></span>
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
                <div class="bg-primary-light text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center mb-4" style="width: 72px; height: 72px;">
                    <i class="bi bi-graph-up-arrow fs-2"></i>
                </div>
                <h5 class="fw-bold">Ready to Scale?</h5>
                <p class="text-muted small mb-4">"Dropshipping Made Simple, Profitable, and Powerful." Browse our catalog and start adding orders to earn real profit.</p>
                <a href="/store/products" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                    <i class="bi bi-shop-window me-2"></i> Browse Products
                </a>
            </div>
        </div>
        <div class="card border-0 shadow-sm bg-secondary text-white">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-2">Grow with EMAG.PK</h6>
                <p class="small text-white-50 mb-0">"Wholesale Prices, World-Class Service." We handle the fulfillment, you handle the growth.</p>
            </div>
        </div>
    </div>
</div>
