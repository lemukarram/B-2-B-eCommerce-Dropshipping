<?php $pageTitle = 'Sellers & Stores'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Sellers & Stores</h4>
</div>

<ul class="nav nav-tabs mb-3">
    <?php foreach (['approved' => 'Active', 'pending' => 'Pending Approval', 'suspended' => 'Suspended'] as $s => $label): ?>
    <li class="nav-item">
        <a class="nav-link <?= ($status === $s) ? 'active' : '' ?>" href="/admin/sellers?status=<?= $s ?>">
            <?= $label ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Business</th>
                    <th>Email</th>
                    <th>City</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sellers as $s): ?>
                <tr>
                    <td class="fw-medium"><?= e($s['name']) ?></td>
                    <td><span class="badge bg-<?= $s['role'] === 'seller' ? 'primary' : 'info' ?>"><?= ucfirst(e($s['role'])) ?></span></td>
                    <td><?= e($s['business_name'] ?? '—') ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td><?= e($s['city'] ?? '—') ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    <td>
                        <a href="/admin/sellers/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <?php if ($status === 'pending'): ?>
                        <form method="POST" action="/admin/sellers/<?= $s['id'] ?>/approve" class="d-inline">
                            <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                            <button class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sellers)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$baseUrl    = '/admin/sellers';
$queryExtra = '&status=' . urlencode($status);
include VIEW_PATH . '/components/pagination.php';
?>
