<?php $pageTitle = 'My Orders'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>My Orders</h4>
    <a href="/seller/orders/create" class="btn btn-primary btn-sm">+ Place Order</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>City</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-end">Profit</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $statusColors = [
                    'pending'    => 'warning text-dark',
                    'processing' => 'info text-dark',
                    'shipped'    => 'primary',
                    'delivered'  => 'success',
                    'failed'     => 'danger',
                    'returned'   => 'danger',
                    'cancelled'  => 'secondary',
                ];
                foreach ($orders as $o):
                ?>
                <tr>
                    <td><a href="/seller/orders/<?= $o['id'] ?>" class="fw-medium"><?= e($o['order_number']) ?></a></td>
                    <td>
                        <div><?= e($o['customer_name']) ?></div>
                        <div class="text-muted small"><?= e($o['customer_phone']) ?></div>
                    </td>
                    <td><?= e($o['customer_city']) ?></td>
                    <td class="text-end">PKR <?= number_format($o['total_selling_price'], 0) ?></td>
                    <td class="text-end">
                        <?php if ($o['seller_profit'] !== null): ?>
                            <span class="text-success fw-bold">PKR <?= number_format($o['seller_profit'], 0) ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-<?= $statusColors[$o['status']] ?? 'secondary' ?>"><?= e($o['status']) ?></span></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">
                    No orders yet. <a href="/seller/orders/create">Place your first order</a>.
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$baseUrl = '/seller/orders';
include VIEW_PATH . '/components/pagination.php';
?>
