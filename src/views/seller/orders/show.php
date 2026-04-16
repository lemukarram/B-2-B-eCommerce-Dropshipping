<?php $pageTitle = 'Order ' . e($order['order_number']); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Order <?= e($order['order_number']) ?></h4>
    <a href="/seller/orders" class="btn btn-outline-secondary btn-sm">Back to Orders</a>
</div>

<?php
$statusColors = ['pending' => 'warning text-dark','processing' => 'info text-dark','shipped' => 'primary','delivered' => 'success','failed' => 'danger','returned' => 'danger','cancelled' => 'secondary'];
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">Order Items</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Your Selling Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['product_title']) ?></td>
                            <td class="text-end">PKR <?= number_format($item['selling_price'], 2) ?></td>
                            <td class="text-end"><?= (int)$item['quantity'] ?></td>
                            <td class="text-end">PKR <?= number_format($item['selling_price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end">Selling Price Total:</td>
                            <td class="text-end fw-bold">PKR <?= number_format($order['total_selling_price'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-muted">Base Price Total:</td>
                            <td class="text-end text-muted">- PKR <?= number_format($order['total_base_price'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-muted">Delivery Charge:</td>
                            <td class="text-end text-muted">- PKR <?= number_format($order['delivery_charge'], 2) ?></td>
                        </tr>
                        <tr class="table-success">
                            <td colspan="3" class="text-end fw-bold">Your Profit:</td>
                            <td class="text-end fw-bold text-success fs-5">
                                <?= $order['seller_profit'] !== null
                                    ? 'PKR ' . number_format($order['seller_profit'], 2)
                                    : '<span class="text-muted fs-6">Credited on delivery</span>' ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">Status</div>
            <div class="card-body text-center py-4">
                <span class="badge bg-<?= $statusColors[$order['status']] ?? 'secondary' ?> fs-6 px-3 py-2">
                    <?= ucfirst(e($order['status'])) ?>
                </span>
                <?php if ($order['failure_deduction'] > 0): ?>
                    <div class="mt-2 text-danger small">
                        Deduction applied: PKR <?= number_format($order['failure_deduction'], 2) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Customer</div>
            <div class="card-body small">
                <p class="mb-1 fw-medium"><?= e($order['customer_name']) ?></p>
                <p class="mb-1"><?= e($order['customer_phone']) ?></p>
                <p class="mb-1"><?= e($order['customer_address']) ?></p>
                <p class="mb-0"><?= e($order['customer_city']) ?>, <?= e($order['customer_province']) ?></p>
            </div>
        </div>

        <?php if ($order['notes']): ?>
        <div class="card">
            <div class="card-header">Notes</div>
            <div class="card-body small"><?= nl2br(e($order['notes'])) ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>
