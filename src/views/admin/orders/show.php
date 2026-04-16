<?php $pageTitle = 'Order ' . e($order['order_number']); ?>

<div class="row">
    <!-- Order details -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Order Items</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Base Price</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="fw-medium text-dark"><?= e($item['product_title']) ?></td>
                            <td class="text-end">Rs. <?= number_format($item['base_price_snapshot'], 2) ?></td>
                            <td class="text-end">Rs. <?= number_format($item['selling_price'], 2) ?></td>
                            <td class="text-end"><?= (int)$item['quantity'] ?></td>
                            <td class="text-end fw-bold">Rs. <?= number_format($item['selling_price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="4" class="text-end py-3">Total Selling Price:</td>
                            <td class="text-end fw-bold py-3 text-dark fs-5">Rs. <?= number_format($order['total_selling_price'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-muted small py-2">Total Base Price:</td>
                            <td class="text-end text-muted small py-2">Rs. <?= number_format($order['total_base_price'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end text-muted small py-2">Delivery Charge:</td>
                            <td class="text-end text-muted small py-2">Rs. <?= number_format($order['delivery_charge'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold text-primary py-3">Seller Profit:</td>
                            <td class="text-end fw-bold text-primary py-3 fs-5">
                                <?= $order['seller_profit'] !== null
                                    ? 'Rs. ' . number_format($order['seller_profit'], 2)
                                    : '<span class="text-muted fw-normal fs-6">Pending delivery</span>' ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Update Status -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Status & Logistics</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/orders/<?= $order['id'] ?>/status">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small text-uppercase">New Status</label>
                            <select name="status" class="form-select" id="statusSelect" onchange="toggleDeduction()">
                                <?php foreach (['pending','processing','shipped','delivered','failed','returned','cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4" id="deductionField" style="display:none;">
                            <label class="form-label fw-semibold small text-uppercase">Failure Deduction (Rs.)</label>
                            <input type="number" name="failure_deduction" class="form-control" value="200" min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Update</button>
                        </div>
                    </div>
                    <div id="deductionHint" class="form-text mt-2" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i> Amount to deduct from seller's wallet for delivery failure.
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold">Customer Details</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= e($order['customer_name']) ?></div>
                        <div class="text-muted small"><?= e($order['customer_phone']) ?></div>
                    </div>
                </div>
                <hr class="text-faded">
                <div class="d-flex mb-2">
                    <i class="bi bi-geo-alt text-muted me-2"></i>
                    <div class="small">
                        <?= e($order['customer_address']) ?><br>
                        <?= e($order['customer_city']) ?>, <?= e($order['customer_province']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold">Seller Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success-subtle text-success rounded-circle p-2 me-3">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= e($order['seller_name']) ?></div>
                        <div class="text-muted small"><?= e($order['seller_email']) ?></div>
                    </div>
                </div>
                <a href="/admin/sellers/<?= $order['user_id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                    View Seller Profile
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold">Order Metadata</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush small">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="text-muted">Current Status</span>
                        <?php 
                            $statusClass = match($order['status']) {
                                'pending' => 'bg-warning-subtle text-warning',
                                'processing' => 'bg-info-subtle text-info',
                                'delivered' => 'bg-success-subtle text-success',
                                'cancelled' => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary'
                            };
                        ?>
                        <span class="badge <?= $statusClass ?> rounded-pill px-3">
                            <?= ucfirst(e($order['status'])) ?>
                        </span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between py-3">
                        <span class="text-muted">Order Placed</span>
                        <span class="fw-medium"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <?php if ($order['profit_credited_at']): ?>
                    <div class="list-group-item d-flex justify-content-between py-3">
                        <span class="text-muted">Profit Credited</span>
                        <span class="text-success fw-bold"><?= date('d M Y', strtotime($order['profit_credited_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['notes']): ?>
                    <div class="list-group-item py-3">
                        <span class="text-muted d-block mb-1">Internal Notes</span>
                        <p class="mb-0 bg-light p-2 rounded small"><?= e($order['notes']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDeduction() {
    const s = document.getElementById('statusSelect').value;
    const field = document.getElementById('deductionField');
    const hint = document.getElementById('deductionHint');
    const isFailure = (s === 'failed' || s === 'returned');
    field.style.display = isFailure ? '' : 'none';
    hint.style.display = isFailure ? '' : 'none';
}
toggleDeduction();
</script>
