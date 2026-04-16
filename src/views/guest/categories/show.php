<?php $pageTitle = e($category['name']); ?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/categories">Categories</a></li>
        <li class="breadcrumb-item active"><?= e($category['name']) ?></li>
    </ol>
</nav>

<div class="d-flex align-items-center mb-4 gap-3">
    <?php if ($category['image']): ?>
        <img src="<?= e($category['image']) ?>" style="height:60px;width:60px;object-fit:cover;" class="rounded">
    <?php endif; ?>
    <div>
        <h4 class="mb-0"><?= e($category['name']) ?></h4>
        <?php if ($category['description']): ?>
            <p class="text-muted mb-0"><?= e($category['description']) ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Subcategories -->
<?php if (!empty($children)): ?>
<div class="row g-2 mb-4">
    <?php foreach ($children as $child): ?>
    <div class="col-auto">
        <a href="/categories/<?= e($child['slug']) ?>" class="btn btn-outline-secondary btn-sm"><?= e($child['name']) ?></a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Products grid — NO prices shown to guests -->
<div class="row g-3">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100">
            <?php $img = \App\Models\Product::primaryImage($p['id']); ?>
            <?php if ($img): ?>
                <img src="<?= e($img['image_path']) ?>" class="card-img-top product-thumb" alt="<?= e($p['title']) ?>">
            <?php else: ?>
                <div class="card-img-top product-thumb bg-light d-flex align-items-center justify-content-center">
                    <i class="bi bi-image fs-1 text-muted"></i>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <h6 class="card-title"><?= e($p['title']) ?></h6>
                <a href="/products/<?= e($p['slug']) ?>" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
    <div class="col-12 text-center text-muted py-5">No products in this category yet.</div>
    <?php endif; ?>
</div>

<?php
$baseUrl    = '/categories/' . e($category['slug']);
include VIEW_PATH . '/components/pagination.php';
?>
