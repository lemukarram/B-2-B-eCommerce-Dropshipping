<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">My Orders</h2>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="/store/products" class="btn btn-primary px-4 rounded-pill">
            <i class="bi bi-cart-plus me-1"></i> Place New Order
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Selling Total</th>
                    <th>Your Profit</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="ps-4 fw-bold"><?= $order['order_number'] ?></td>
                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    <td>
                        <div class="fw-medium"><?= e($order['customer_name']) ?></div>
                        <small class="text-muted"><?= e($order['customer_city']) ?></small>
                    </td>
                    <td>Rs. <?= number_format((float)$order['total_selling_price'], 2) ?></td>
                    <td class="text-success fw-bold">Rs. <?= number_format((float)$order['store_profit'], 2) ?></td>
                    <td>
                        <?php
                        $badgeClass = match($order['status']) {
                            'pending'    => 'bg-warning-subtle text-warning border-warning-subtle',
                            'delivered'  => 'bg-success-subtle text-success border-success-subtle',
                            'failed'     => 'bg-danger-subtle text-danger border-danger-subtle',
                            'cancelled'  => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                            default      => 'bg-info-subtle text-info border-info-subtle',
                        };
                        ?>
                        <span class="badge rounded-pill border <?= $badgeClass ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <a href="/store/orders/<?= $order['id'] ?>" class="btn btn-sm btn-light rounded-pill px-3">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted mb-3">No orders placed yet.</div>
                        <a href="/store/products" class="btn btn-outline-primary btn-sm rounded-pill">Browse Products</a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    <?php include VIEW_PATH . '/components/pagination.php'; ?>
</div>
