<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'EMAG.PK') ?> — B2B Dropshipping</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="/">EMAG<span class="text-primary">.PK</span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link px-3" href="/">Home</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="/categories">Categories</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="/products">Products</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="/faqs">Help</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="/login" class="btn btn-outline-primary border-0">Login</a>
                <a href="/register" class="btn btn-primary px-4">Get Started</a>
            </div>
        </div>
    </div>
</nav>

<main class="py-5 animate-fade-in">
    <div class="container">
        <?php include VIEW_PATH . '/components/flash.php'; ?>
        <?= $content ?>
    </div>
</main>

<footer class="bg-white border-top py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <a class="navbar-brand fw-bold fs-3 mb-3 d-inline-block" href="/">EMAG<span class="text-primary">.PK</span></a>
                <p class="text-muted small mb-4 pe-lg-5">"Dropshipping Made Simple, Profitable, and Powerful." Your ultimate partner in B2B commerce and fulfillment excellence.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-muted fs-5"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2 mb-4 mb-lg-0">
                <h6 class="fw-bold mb-3">Company</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/about" class="text-decoration-none text-muted">Who We Are</a></li>
                    <li class="mb-2"><a href="/how-it-works" class="text-decoration-none text-muted">How We Work</a></li>
                    <li class="mb-2"><a href="/contact" class="text-decoration-none text-muted">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2 mb-4 mb-lg-0">
                <h6 class="fw-bold mb-3">Support</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/faqs" class="text-decoration-none text-muted">Help Center</a></li>
                    <li class="mb-2"><a href="/how-to-register" class="text-decoration-none text-muted">How to Register</a></li>
                    <li class="mb-2"><a href="/privacy" class="text-decoration-none text-muted">Privacy Policy</a></li>
                    <li class="mb-2"><a href="/terms" class="text-decoration-none text-muted">Terms of Service</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-bold mb-3">Join Our Network</h6>
                <p class="small text-muted mb-4">Empowering over 500+ active stores across Pakistan. Scale your business today.</p>
                <a href="/register" class="btn btn-primary btn-sm rounded-pill px-4">Create Free Account</a>
            </div>
        </div>
        <hr class="my-5 opacity-5">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted small">&copy; <?= date('Y') ?> EMAG.PK — International B2B Solutions. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 text-muted small">Wholesale Prices, World-Class Service.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
