<?php $pageTitle = 'Products'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Product Catalogue</h4>
    <div class="btn-group">
        <a href="/seller/products/export/shopify" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export Shopify
        </a>
        <a href="/seller/products/export/wordpress" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export WordPress
        </a>
        <a href="/seller/orders/create" class="btn btn-primary btn-sm">+ Place Order</a>
    </div>
</div>

<!-- Category filter -->
<form class="row g-2 mb-3" method="GET">
    <div class="col-auto">
        <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == ($_GET['category_id'] ?? '')) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<div class="row g-3">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card h-100">
            <a href="/seller/products/<?= e($p['slug']) ?>">
                <?php $img = \App\Models\Product::primaryImage($p['id']); ?>
                <?php if ($img): ?>
                    <img src="<?= e($img['image_path']) ?>" class="card-img-top product-thumb" alt="<?= e($p['title']) ?>">
                <?php else: ?>
                    <div class="card-img-top product-thumb bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-image fs-1 text-muted"></i>
                    </div>
                <?php endif; ?>
            </a>
            <div class="card-body d-flex flex-column">
                <p class="card-title mb-1 fw-medium">
                    <a href="/seller/products/<?= e($p['slug']) ?>" class="text-decoration-none text-dark"><?= e($p['title']) ?></a>
                </p>
                <p class="text-muted small mb-1"><?= e($p['category_name'] ?? '') ?></p>
                <p class="fw-bold text-primary mb-2">Base: PKR <?= number_format($p['base_price'], 2) ?></p>
                <div class="mt-auto">
                    <?php if ($p['stock_quantity'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">In Stock (<?= (int)$p['stock_quantity'] ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
    <div class="col-12 text-center text-muted py-5">No products available.</div>
    <?php endif; ?>
</div>

<?php
$baseUrl    = '/seller/products';
$queryExtra = isset($_GET['category_id']) ? '&category_id=' . (int)$_GET['category_id'] : '';
include VIEW_PATH . '/components/pagination.php';
?>
