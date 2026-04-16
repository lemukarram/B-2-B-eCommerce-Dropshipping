<?php $pageTitle = 'My Connected Stores'; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Active Stores</h5>
            <small class="text-muted">Total: <?= count($stores) ?> stores registered under your network</small>
        </div>
        <div class="d-flex gap-2">
             <!-- Share link to register store under this seller -->
             <button class="btn btn-sm btn-outline-primary" onclick="copyInviteLink()">
                <i class="bi bi-share me-1"></i> Invite Store
             </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Store Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stores as $store): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3">
                                <i class="bi bi-shop"></i>
                            </div>
                            <span class="fw-bold text-dark"><?= e($store['name']) ?></span>
                        </div>
                    </td>
                    <td><?= e($store['email']) ?></td>
                    <td><?= e($store['phone'] ?? 'N/A') ?></td>
                    <td class="text-muted small"><?= date('M d, Y', strtotime($store['created_at'])) ?></td>
                    <td>
                        <span class="badge bg-success-subtle text-success border-0 rounded-pill px-3">
                            Active
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="/seller/stores/<?= e($store['id']) ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($stores)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-shop text-muted fs-1 d-block mb-2"></i>
                        <span class="text-muted">No stores have joined your network yet.</span>
                        <div class="mt-3">
                            <button class="btn btn-primary" onclick="copyInviteLink()">
                                <i class="bi bi-person-plus me-1"></i> Copy Invite Link
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyInviteLink() {
    const link = window.location.origin + '/register/store?ref=<?= \Core\Auth::id() ?>';
    navigator.clipboard.writeText(link).then(() => {
        alert('Invite link copied to clipboard!');
    });
}
</script>
