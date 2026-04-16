<?php $pageTitle = 'Network Orders'; ?>

<div class="row g-3 mb-4 align-items-center">
    <div class="col-md-4">
        <h4 class="mb-0 fw-bold">System Orders</h4>
    </div>
    <div class="col-md-8">
        <ul class="nav nav-pills justify-content-md-end gap-2">
            <?php foreach (['' => 'All Orders', 'pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'failed' => 'Failed', 'returned' => 'Returned', 'cancelled' => 'Cancelled'] as $s => $label): ?>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 <?= ($status === $s) ? 'active bg-primary' : 'bg-white border text-dark' ?>" 
                   style="font-size: 0.85rem; font-weight: 500; border-radius: 0.5rem;"
                   href="/admin/orders<?= $s ? '?status=' . $s : '' ?>">
                    <?= $label ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Seller</th>
                    <th>Customer</th>
                    <th>City</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Profit</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $statusColors = [
                    'pending'    => 'warning',
                    'processing' => 'info',
                    'shipped'    => 'primary',
                    'delivered'  => 'success',
                    'failed'     => 'danger',
                    'returned'   => 'danger',
                    'cancelled'  => 'secondary',
                ];
                foreach ($orders as $o):
                    $color = $statusColors[$o['status']] ?? 'secondary';
                ?>
                <tr>
                    <td>
                        <a href="/admin/orders/<?= $o['id'] ?>" class="fw-bold text-primary text-decoration-none">
                            #<?= e($o['order_number']) ?>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-1 me-2">
                                <i class="bi bi-person small"></i>
                            </div>
                            <span class="small fw-medium"><?= e($o['seller_name']) ?></span>
                        </div>
                    </td>
                    <td><span class="small"><?= e($o['customer_name']) ?></span></td>
                    <td><span class="badge bg-light text-dark border fw-normal"><?= e($o['customer_city']) ?></span></td>
                    <td class="text-end fw-bold">Rs. <?= number_format($o['total_selling_price'], 0) ?></td>
                    <td class="text-end text-success fw-bold">
                        <?= $o['seller_profit'] !== null
                            ? 'Rs. ' . number_format($o['seller_profit'], 0)
                            : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border-0 rounded-pill px-3">
                            <?= ucfirst(e($o['status'])) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td class="text-end">
                        <a href="/admin/orders/<?= $o['id'] ?>" class="btn btn-sm btn-light border">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bi bi-cart-x text-muted fs-1 d-block mb-2"></i>
                        <span class="text-muted">No orders found matching your criteria.</span>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <?php
    $baseUrl    = '/admin/orders';
    $queryExtra = $status ? '&status=' . urlencode($status) : '';
    include VIEW_PATH . '/components/pagination.php';
    ?>
</div>
