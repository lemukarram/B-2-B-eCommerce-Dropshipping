<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold mb-0">Products <small class="text-muted fw-normal fs-6">Wholesale Catalog</small></h2>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="/store/orders/create" class="btn btn-primary px-4 rounded-pill">
            <i class="bi bi-cart-plus me-1"></i> Place New Order
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm p-3">
            <form action="/store/products" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="category_id" class="form-select border-0 bg-light rounded-pill" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (int)($_GET['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control border-0 bg-light rounded-pill" placeholder="Search products..." value="<?= e($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100 rounded-pill">Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($products as $product): ?>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm overflow-hidden product-card">
            <?php $img = \App\Models\Product::primaryImage($product['id']); ?>
            <div class="product-img-wrapper" style="height: 200px; background: #f8f9fa;">
                <?php if ($img): ?>
                    <img src="<?= e($img['image_path']) ?>" class="card-img-top w-100 h-100 object-fit-cover" alt="<?= e($product['title']) ?>">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">No Image</div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted small"><?= e($product['category_name']) ?></h6>
                <h5 class="card-title fs-6 mb-3 line-clamp-2" style="height: 2.5rem; overflow: hidden;"><?= e($product['title']) ?></h5>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Wholesale</small>
                        <span class="fw-bold text-dark fs-5">Rs. <?= number_format((float)$product['wholesale_price'], 2) ?></span>
                    </div>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">In Stock</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pb-3 pt-0">
                <a href="/store/products/<?= $product['slug'] ?>" class="btn btn-outline-dark w-100 rounded-pill btn-sm">View Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($products)): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="bi bi-search fs-1 text-muted mb-3"></i>
            <h4>No products found</h4>
            <p class="text-muted">Try adjusting your filters or search keywords.</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-center">
    <?php include VIEW_PATH . '/components/pagination.php'; ?>
</div>

<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important;
}
.object-fit-cover {
    object-fit: cover;
}
</style>
