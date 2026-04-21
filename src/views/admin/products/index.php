<?php $pageTitle = 'Product Inventory'; ?>

<div class="row g-3 mb-4 align-items-center">
    <div class="col-md-4">
        <h4 class="mb-0 fw-bold">System Inventory</h4>
    </div>
    <div class="col-md-8">
        <div class="d-flex justify-content-md-end gap-2">
            <form class="d-flex gap-2" method="GET" action="/admin/products">
                <select name="category_id" class="form-select form-select-sm" style="min-width: 180px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == ($_GET['category_id'] ?? '')) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($_GET['category_id']) && $_GET['category_id'] !== ''): ?>
                    <a href="/admin/products" class="btn btn-sm btn-light border" title="Clear Filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </form>
            <a href="/admin/products/bulk-upload" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cloud-arrow-up me-1"></i> Bulk Upload
            </a>
            <a href="/admin/products/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> New Product
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">Image</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th class="text-end" style="width: 150px;">Base Price</th>
                    <th class="text-center">Stock</th>
                    <th style="width: 140px;">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if ($p['image_path']): ?>
                            <img src="<?= e($p['image_path']) ?>" alt="" class="rounded border" style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                <i class="bi bi-image small"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?= e($p['title']) ?></div>
                        <div class="text-muted small">
                            ID: #<?= $p['id'] ?> | SKU: <span class="font-monospace"><?= e($p['sku']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info border-0 rounded-pill px-3">
                            <?= e($p['category_name'] ?? 'Uncategorized') ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="input-group input-group-sm justify-content-end">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" 
                                   class="form-control quick-update" 
                                   data-id="<?= $p['id'] ?>" 
                                   data-field="base_price" 
                                   value="<?= $p['base_price'] ?>" 
                                   step="0.01" 
                                   style="max-width: 100px;">
                        </div>
                    </td>
                    <td class="text-center">
                        <?php if ($p['stock_quantity'] <= 0): ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php elseif ($p['stock_quantity'] < 10): ?>
                            <span class="badge bg-warning text-dark"><?= (int)$p['stock_quantity'] ?> Low</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border"><?= (int)$p['stock_quantity'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <select class="form-select form-select-sm quick-update" 
                                data-id="<?= $p['id'] ?>" 
                                data-field="is_active">
                            <option value="1" <?= $p['is_active'] ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= !$p['is_active'] ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/admin/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-light border" title="Edit Product">
                                <i class="bi bi-pencil-square text-primary"></i>
                            </a>
                            <form method="POST" action="/admin/products/<?= $p['id'] ?>/delete" class="d-inline"
                                  onsubmit="return confirm('Delete this product?')">
                                <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                <button type="submit" class="btn btn-sm btn-light border" title="Delete Product">
                                    <i class="bi bi-trash3 text-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-box-seam text-muted fs-1 d-block mb-2"></i>
                        <span class="text-muted">No products found in the system.</span>
                        <div class="mt-3">
                            <a href="/admin/products/create" class="btn btn-sm btn-primary">Add Your First Product</a>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <?php
    $baseUrl    = '/admin/products';
    $queryExtra = isset($_GET['category_id']) ? '&category_id=' . (int)$_GET['category_id'] : '';
    include VIEW_PATH . '/components/pagination.php';
    ?>
</div>

<script>
document.querySelectorAll('.quick-update').forEach(el => {
    el.addEventListener('change', async function() {
        const id = this.dataset.id;
        const field = this.dataset.field;
        const value = this.value;
        const originalColor = this.style.borderColor;
        
        // Find CSRF token from any existing form
        const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value;

        if (!csrfToken) {
            alert('Security token not found. Please refresh the page.');
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('field', field);
        formData.append('value', value);
        formData.append('_csrf_token', csrfToken);

        this.style.borderColor = '#0d6efd'; // Visual feedback

        try {
            const response = await fetch('/admin/products/quick-update', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.style.borderColor = '#198754';
                setTimeout(() => {
                    this.style.borderColor = originalColor;
                }, 1000);
            } else {
                alert(result.error || 'Update failed');
                this.style.borderColor = '#dc3545';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            this.style.borderColor = '#dc3545';
        }
    });
});
</script>
