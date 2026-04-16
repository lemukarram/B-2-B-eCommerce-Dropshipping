<?php $pageTitle = 'Seller Performance'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Seller Performance</h4>
    <a href="/admin/reports" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Seller</th>
                    <th>Business</th>
                    <th class="text-end">Total Orders</th>
                    <th class="text-end">Delivered</th>
                    <th class="text-end">Total Earned</th>
                    <th class="text-end">Withdrawn</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sellers as $s): ?>
                <tr>
                    <td>
                        <a href="/admin/sellers/<?= $s['id'] ?>"><?= e($s['name']) ?></a>
                        <div class="text-muted small"><?= e($s['email']) ?></div>
                    </td>
                    <td><?= e($s['business_name'] ?? '—') ?></td>
                    <td class="text-end"><?= (int)$s['total_orders'] ?></td>
                    <td class="text-end"><?= (int)$s['delivered_orders'] ?></td>
                    <td class="text-end">PKR <?= number_format($s['total_earned'] ?? 0, 0) ?></td>
                    <td class="text-end">PKR <?= number_format($s['total_withdrawn'] ?? 0, 0) ?></td>
                    <td class="text-end fw-bold text-success">PKR <?= number_format($s['balance'] ?? 0, 0) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sellers)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No sellers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
