<?php $pageTitle = 'Home'; ?>

<div class="row align-items-center py-5 mb-5">
    <div class="col-lg-6">
        <h1 class="display-3 fw-bold mb-3">Your Gateway to <span class="text-primary">B2B Dropshipping</span></h1>
        <p class="lead text-muted mb-4 fs-4">Scale your business with Pakistan's most advanced dropshipping network. Connect sellers and stores in one seamless platform.</p>
        <div class="d-flex gap-3">
            <a href="/products" class="btn btn-primary btn-lg px-4 py-3 shadow-sm">
                <i class="bi bi-bag-check me-2"></i> Browse Products
            </a>
            <a href="/register" class="btn btn-outline-dark btn-lg px-4 py-3">
                Join the Network
            </a>
        </div>
        <div class="mt-5 d-flex align-items-center gap-4">
            <div>
                <h4 class="fw-bold mb-0">1,000+</h4>
                <small class="text-muted">Active Products</small>
            </div>
            <div class="vr"></div>
            <div>
                <h4 class="fw-bold mb-0">500+</h4>
                <small class="text-muted">Verified Sellers</small>
            </div>
            <div class="vr"></div>
            <div>
                <h4 class="fw-bold mb-0">2.5k+</h4>
                <small class="text-muted">Stores Joined</small>
            </div>
        </div>
    </div>
    <div class="col-lg-6 d-none d-lg-block">
        <div class="position-relative">
            <div class="bg-primary rounded-circle position-absolute top-50 start-50 translate-middle opacity-10" style="width: 500px; height: 500px;"></div>
            <img src="/assets/img/hero-placeholder.png" class="img-fluid position-relative z-1" alt="B2B Network" onerror="this.src='https://img.freepik.com/free-vector/logistics-network-background_23-2148767735.jpg?t=st=1713000000&exp=1713003600&hmac=7e3d64115469950d85a083a21558913928a64010538a83d4638a16584f23b7b2'; this.style.borderRadius='1.5rem'; this.style.boxShadow='var(--card-shadow-hover)';">
        </div>
    </div>
</div>

<div class="py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-0">Shop by Category</h2>
            <p class="text-muted">Discover products across our curated categories</p>
        </div>
        <a href="/categories" class="btn btn-link text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (!empty($categories)): ?>
    <div class="row g-4">
        <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
        <div class="col-6 col-md-3">
            <a href="/categories/<?= e($cat['slug']) ?>" class="text-decoration-none group">
                <div class="card border-0 shadow-sm h-100 text-center overflow-hidden transition-all hover-translate-y">
                    <div class="bg-light py-4">
                        <?php if ($cat['image']): ?>
                            <img src="<?= e($cat['image']) ?>" class="img-fluid mb-3 px-4" style="height: 120px; object-fit: contain;" alt="<?= e($cat['name']) ?>">
                        <?php else: ?>
                            <i class="bi bi-collection fs-1 text-primary opacity-50 d-block mb-3 py-4"></i>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-0 text-dark"><?= e($cat['name']) ?></h6>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- How It Works Section -->
<div class="py-5 mt-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">How It <span class="text-primary">Works</span></h2>
        <p class="text-muted">Start your business journey in 4 simple steps</p>
    </div>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center">
                <div class="bg-primary-light rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-plus text-primary fs-3"></i>
                </div>
                <h5 class="fw-bold">1. Register</h5>
                <p class="small text-muted mb-0">Create your free account and gain instant access to our catalog.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center">
                <div class="bg-primary-light rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-shop text-primary fs-3"></i>
                </div>
                <h5 class="fw-bold">2. Browse</h5>
                <p class="small text-muted mb-0">Choose from thousands of high-demand wholesale products.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center">
                <div class="bg-primary-light rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-megaphone text-primary fs-3"></i>
                </div>
                <h5 class="fw-bold">3. Promote</h5>
                <p class="small text-muted mb-0">Promote products on social media and set your own profit margins.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4 text-center">
                <div class="bg-primary-light rounded-circle p-3 mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-box-seam text-primary fs-3"></i>
                </div>
                <h5 class="fw-bold">4. Earn</h5>
                <p class="small text-muted mb-0">We handle shipping. You collect the profits directly in your wallet.</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-4 p-5 shadow-sm border mt-5">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold">Ready to start your dropshipping journey?</h2>
            <p class="text-muted mb-md-0">Register today and get access to thousands of products at wholesale prices.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="/register" class="btn btn-primary btn-lg px-5">Get Started Now</a>
        </div>
    </div>
</div>
