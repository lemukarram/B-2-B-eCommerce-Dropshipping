<?php $pageTitle = 'Administration Overview'; ?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary-subtle text-primary rounded-3 p-2 me-3">
                        <i class="bi bi-cart-fill fs-4"></i>
                    </div>
                    <h6 class="card-subtitle text-muted mb-0">Total Orders</h6>
                </div>
                <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['total_orders']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning-subtle text-warning rounded-3 p-2 me-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <h6 class="card-subtitle text-muted mb-0">Pending Orders</h6>
                </div>
                <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['pending_orders']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success-subtle text-success rounded-3 p-2 me-3">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <h6 class="card-subtitle text-muted mb-0">Active Sellers</h6>
                </div>
                <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['total_sellers']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-danger-subtle text-danger rounded-3 p-2 me-3">
                        <i class="bi bi-person-plus-fill fs-4"></i>
                    </div>
                    <h6 class="card-subtitle text-muted mb-0">Pending Approvals</h6>
                </div>
                <h2 class="card-title fw-bold mb-0"><?= number_format((int)$stats['pending_sellers']) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Network Orders</h5>
                <a href="/admin/orders" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Seller</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td class="fw-medium">#<?= e($order['order_number']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-1 me-2">
                                        <i class="bi bi-person small"></i>
                                    </div>
                                    <?= e($order['seller_name']) ?>
                                </div>
                            </td>
                            <td class="fw-bold">Rs. <?= number_format($order['total_selling_price'], 0) ?></td>
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
                                <span class="text-muted">No system orders recorded.</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Quick Links</h5>
            </div>
            <div class="list-group list-group-flush border-top">
                <a href="/admin/products/bulk-upload" class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                    <div class="d-flex w-100 align-items-center">
                        <i class="bi bi-cloud-arrow-up-fill fs-4 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Bulk Upload</h6>
                            <small class="text-muted">Import products via CSV/Excel</small>
                        </div>
                    </div>
                </a>
                <a href="/admin/reports" class="list-group-item list-group-item-action py-3 border-0 border-bottom">
                    <div class="d-flex w-100 align-items-center">
                        <i class="bi bi-bar-chart-fill fs-4 text-success me-3"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">System Reports</h6>
                            <small class="text-muted">Analyze network performance</small>
                        </div>
                    </div>
                </a>
                <a href="/admin/settings" class="list-group-item list-group-item-action py-3 border-0">
                    <div class="d-flex w-100 align-items-center">
                        <i class="bi bi-gear-fill fs-4 text-secondary me-3"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Global Settings</h6>
                            <small class="text-muted">Configure platform parameters</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
