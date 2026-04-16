<?php $pageTitle = e($product['title']); ?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/products">Products</a></li>
        <li class="breadcrumb-item active"><?= e($product['title']) ?></li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-5">
        <?php if (!empty($images)): ?>
        <div id="productImages" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner rounded">
                <?php foreach ($images as $i => $img): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <img src="<?= e($img['image_path']) ?>" class="d-block w-100"
                         style="max-height:400px;object-fit:contain;" alt="<?= e($product['title']) ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($images) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#productImages" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productImages" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:300px;">
            <i class="bi bi-image fs-1 text-muted"></i>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-7">
        <h3><?= e($product['title']) ?></h3>
        <p class="text-muted"><?= e($product['category_name'] ?? '') ?></p>

        <?php if ($product['description']): ?>
            <p><?= nl2br(e($product['description'])) ?></p>
        <?php endif; ?>

        <!-- Price deliberately hidden from guests -->
        <div class="alert alert-info">
            <strong>Are you a seller?</strong>
            <a href="/login" class="alert-link">Login</a> or <a href="/register" class="alert-link">register</a>
            to view pricing and place orders.
        </div>
    </div>
</div>
