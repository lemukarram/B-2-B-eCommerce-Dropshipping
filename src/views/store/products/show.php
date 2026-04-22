<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store/products">Catalog</a></li>
            <li class="breadcrumb-item active"><?= e($product['title']) ?></li>
        </ol>
    </nav>
</div>

<div class="row g-5">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden rounded-4">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner" style="height: 500px; background: #f8f9fa;">
                    <?php if (empty($images)): ?>
                        <div class="carousel-item active h-100 d-flex align-items-center justify-content-center text-muted">
                            No images available
                        </div>
                    <?php else: ?>
                        <?php foreach ($images as $i => $img): ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?> h-100">
                                <img src="<?= e($img['image_path']) ?>" class="d-block w-100 h-100 object-fit-contain" alt="...">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ps-md-4">
            <h6 class="text-primary fw-bold text-uppercase small mb-2"><?= e($product['category_name']) ?></h6>
            <h1 class="display-6 fw-bold mb-3"><?= e($product['title']) ?></h1>
            <p class="text-muted small mb-4">SKU: <?= e($product['sku']) ?></p>

            <div class="bg-light p-4 rounded-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block">Your Wholesale Price</small>
                        <span class="fs-2 fw-bold text-dark">Rs. <?= number_format((float)$product['wholesale_price'], 2) ?></span>
                    </div>
                    <div class="col-6 ps-4">
                        <small class="text-muted d-block">Availability</small>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $product['stock_quantity'] ?>)</span>
                        <?php else: ?>
                            <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold mb-3">Description</h5>
                <div class="product-description text-muted" style="white-space: pre-line;">
                    <?= nl2br(e($product['description'])) ?>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="/store/orders/create?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-lg py-3 rounded-pill fw-bold <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>">
                    <i class="bi bi-cart-plus me-2"></i> Place Order for This Item
                </a>
                <button class="btn btn-outline-dark btn-lg py-3 rounded-pill fw-bold">
                    <i class="bi bi-download me-2"></i> Download Marketing Assets
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.object-fit-contain {
    object-fit: contain;
}
.product-description {
    line-height: 1.6;
}
</style>
