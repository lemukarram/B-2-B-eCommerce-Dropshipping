<?php $pageTitle = 'Categories'; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">System Categories</h5>
        <a href="/admin/categories/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Category
        </a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Name</th>
                    <th>Parent Category</th>
                    <th class="text-center">Sort</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $catMap = array_column($categories, 'name', 'id');
                foreach ($categories as $cat):
                ?>
                <tr>
                    <td>
                        <?php if ($cat['image']): ?>
                            <img src="<?= e($cat['image']) ?>" style="width:48px;height:48px;object-fit:cover;" class="rounded-3 shadow-sm">
                        <?php else: ?>
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width:48px;height:48px;">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?= e($cat['name']) ?></div>
                        <div class="text-muted small">ID: #<?= $cat['id'] ?></div>
                    </td>
                    <td>
                        <?php if ($cat['parent_id']): ?>
                            <span class="badge bg-light text-dark fw-medium border">
                                <i class="bi bi-arrow-return-right me-1 text-primary"></i>
                                <?= e($catMap[$cat['parent_id']] ?? '—') ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">Root Category</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-secondary fw-bold"><?= (int)$cat['sort_order'] ?></span>
                    </td>
                    <td>
                        <?php if ($cat['is_active']): ?>
                            <span class="badge bg-success-subtle text-success border-0 rounded-pill px-3">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border-0 rounded-pill px-3">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/admin/categories/<?= $cat['id'] ?>/edit" class="btn btn-sm btn-light border" title="Edit Category">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </a>
                            <form method="POST" action="/admin/categories/<?= $cat['id'] ?>/delete" class="d-inline"
                                  onsubmit="return confirm('Delete category? Products in it will become uncategorized.')">
                                <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                <button type="submit" class="btn btn-sm btn-light border" title="Delete Category">
                                    <i class="bi bi-trash3 text-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-tags text-muted fs-1 d-block mb-2"></i>
                        <span class="text-muted">No categories defined yet.</span>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
