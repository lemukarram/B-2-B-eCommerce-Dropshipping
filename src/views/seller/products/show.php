<?php $pageTitle = e($product['title']); ?>
<div class="mb-3">
    <a href="/seller/products" class="text-muted small">← Back to Products</a>
</div>

<div class="row">
    <div class="col-md-5">
        <?php if (!empty($images)): ?>
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded">
                    <?php foreach ($images as $i => $img): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <img src="<?= e($img['image_path']) ?>" class="d-block w-100" style="max-height:400px;object-fit:contain;" alt="">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
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
        <h4><?= e($product['title']) ?></h4>
        <p class="text-muted">Category: <?= e($product['category_name'] ?? '—') ?></p>
        <p class="text-muted small font-monospace">SKU: <?= e($product['sku']) ?></p>

        <div class="alert alert-primary">
            <div class="fw-bold fs-5">Base Price: PKR <?= number_format($product['base_price'], 2) ?></div>
            <div class="small text-muted mt-1">Your selling price must exceed this + delivery charge to make profit.</div>
        </div>

        <p><?= nl2br(e($product['description'])) ?></p>

        <a href="/seller/orders/create?product_id=<?= $product['id'] ?>" class="btn btn-primary">
            Order This Product
        </a>
    </div>
</div>
