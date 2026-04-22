<?php $pageTitle = 'Stores'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Stores</h4>
</div>

<ul class="nav nav-tabs mb-3">
    <?php foreach (['approved' => 'Active', 'pending' => 'Pending Approval', 'suspended' => 'Suspended'] as $s => $label): ?>
    <li class="nav-item">
        <a class="nav-link <?= ($status === $s) ? 'active' : '' ?>" href="/admin/stores?status=<?= $s ?>">
            <?= $label ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Store Name</th>
                    <th>Email</th>
                    <th>Parent Seller</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stores as $s): ?>
                <tr>
                    <td class="fw-bold text-dark"><?= e($s['name']) ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td>
                        <?php if($s['parent_seller_name']): ?>
                            <span class="text-primary fw-medium"><?= e($s['parent_seller_name']) ?></span>
                        <?php else: ?>
                            <span class="text-muted small">Direct System</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    <td>
                        <a href="/admin/stores/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($stores)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No stores found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$baseUrl    = '/admin/stores';
$queryExtra = '&status=' . urlencode($status);
include VIEW_PATH . '/components/pagination.php';
?>
