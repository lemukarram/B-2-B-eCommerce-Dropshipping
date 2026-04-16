<?php $pageTitle = 'Explore Our Catalog'; ?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h1 class="fw-bold mb-0">Browse Products</h1>
        <p class="text-muted mb-0">Discover high-quality items for your dropshipping store</p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Filter by Category
            </button>
            <ul class="dropdown-menu shadow-sm border-0">
                <li><a class="dropdown-menu-item px-3 py-2 d-block text-decoration-none text-dark small" href="/products">All Categories</a></li>
                <!-- Category links could be dynamic here if passed from controller -->
            </ul>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 border-0 shadow-sm overflow-hidden group">
            <a href="/products/<?= e($p['slug']) ?>" class="text-decoration-none">
                <div class="position-relative overflow-hidden" style="height: 240px; background: #f1f5f9;">
                    <?php $img = \App\Models\Product::primaryImage($p['id']); ?>
                    <?php if ($img): ?>
                        <img src="<?= e($img['image_path']) ?>" class="w-100 h-100 object-fit-cover transition-all" alt="<?= e($p['title']) ?>" style="transition: transform 0.5s ease;">
                    <?php else: ?>
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-image fs-1 opacity-25"></i>
                        </div>
                    <?php endif; ?>
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover-opacity-100 transition-opacity">
                        <span class="btn btn-light btn-sm w-100 rounded-pill fw-bold">View Details</span>
                    </div>
                </div>
            </a>
            <div class="card-body p-3">
                <small class="text-primary fw-bold text-uppercase mb-1 d-block" style="font-size: 0.65rem; letter-spacing: 0.05em;"><?= e($p['category_name'] ?? 'Uncategorized') ?></small>
                <h6 class="card-title fw-bold text-dark mb-2 text-truncate-2" style="height: 2.5rem; line-height: 1.25rem;"><?= e($p['title']) ?></h6>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small italic">Log in for price</span>
                    <i class="bi bi-arrow-right-circle text-primary fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
    <div class="col-12 text-center py-5">
        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-search fs-2 text-muted"></i>
        </div>
        <h5 class="fw-bold">No products found</h5>
        <p class="text-muted">We couldn't find any products in this category at the moment.</p>
        <a href="/products" class="btn btn-primary mt-2 px-4">Clear All Filters</a>
    </div>
    <?php endif; ?>
</div>

<?php
$baseUrl = '/products';
include VIEW_PATH . '/components/pagination.php';
?>

<style>
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
.group:hover img {
    transform: scale(1.1);
}
</style>
