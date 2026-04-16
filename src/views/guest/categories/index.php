<?php $pageTitle = 'Categories'; ?>
<h4 class="mb-4">All Categories</h4>

<div class="row g-3">
    <?php foreach ($categories as $cat): ?>
    <div class="col-6 col-md-4 col-lg-3">
        <a href="/categories/<?= e($cat['slug']) ?>" class="text-decoration-none">
            <div class="card h-100 text-center">
                <?php if ($cat['image']): ?>
                    <img src="<?= e($cat['image']) ?>" class="card-img-top product-thumb" alt="<?= e($cat['name']) ?>">
                <?php else: ?>
                    <div class="card-img-top product-thumb bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-grid fs-1 text-muted"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h6 class="card-title mb-0"><?= e($cat['name']) ?></h6>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
    <?php if (empty($categories)): ?>
    <div class="col-12 text-center text-muted py-5">No categories available.</div>
    <?php endif; ?>
</div>
