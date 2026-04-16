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
                    <th style="width: 120px;">SKU</th>
                    <th>Product Details</th>
                    <th>Category</th>
                    <th class="text-end">Base Price</th>
                    <th class="text-center">Stock</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <span class="badge bg-light text-secondary border font-monospace"><?= e($p['sku']) ?></span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?= e($p['title']) ?></div>
                        <div class="text-muted small">ID: #<?= $p['id'] ?></div>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info border-0 rounded-pill px-3">
                            <?= e($p['category_name'] ?? 'Uncategorized') ?>
                        </span>
                    </td>
                    <td class="text-end fw-bold">Rs. <?= number_format($p['base_price'], 2) ?></td>
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
                        <?php if ($p['is_active']): ?>
                            <span class="badge bg-success-subtle text-success border-0 rounded-pill px-3">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border-0 rounded-pill px-3">Inactive</span>
                        <?php endif; ?>
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
