<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Seller Panel') ?> — EMAG.PK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="d-flex">
    <div class="sidebar p-3 d-flex flex-column" style="width: 260px; flex-shrink: 0;">
        <a class="navbar-brand fw-bold text-white fs-4 mb-4 px-3" href="/seller">
            EMAG<span class="text-primary">.PK</span>
            <span class="badge bg-primary fs-6 align-middle ms-1" style="font-size: 0.6rem !important;">Seller</span>
        </a>
        
        <div class="nav flex-column flex-grow-1">
            <small class="text-uppercase text-muted fw-bold mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Main</small>
            <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/seller') && strlen($_SERVER['REQUEST_URI']) <= 8 ? 'active' : '' ?>" href="/seller">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>

            <small class="text-uppercase text-muted fw-bold mt-4 mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Business</small>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/seller/products') ? 'active' : '' ?>" href="/seller/products">
                <i class="bi bi-box-seam-fill"></i> My Products
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/seller/markups') ? 'active' : '' ?>" href="/seller/markups">
                <i class="bi bi-percent"></i> Category Markups
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/seller/stores') ? 'active' : '' ?>" href="/seller/stores">
                <i class="bi bi-shop"></i> My Stores
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/seller/orders') ? 'active' : '' ?>" href="/seller/orders">
                <i class="bi bi-cart-fill"></i> Store Orders
            </a>

            <small class="text-uppercase text-muted fw-bold mt-4 mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Finance</small>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/seller/wallet') ? 'active' : '' ?>" href="/seller/wallet">
                <i class="bi bi-wallet2"></i> My Wallet
            </a>
        </div>

        <div class="mt-auto px-3 py-3 border-top border-secondary">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary rounded-circle p-2 me-2">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-white text-truncate fw-medium small"><?= e(\Core\Auth::name()) ?></div>
                    <div class="text-muted text-truncate" style="font-size: 0.75rem;">Verified Seller</div>
                </div>
            </div>
            <a href="/logout" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
        </div>
    </div>

    <main class="flex-grow-1" style="min-width:0; background: var(--bg-light); min-height: 100vh;">
        <header class="bg-white border-bottom px-4 py-3 sticky-top d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold"><?= e($pageTitle ?? 'Dashboard') ?></h5>
            <div class="d-flex align-items-center">
                <a href="/seller/profile" class="text-decoration-none text-dark me-3">
                    <i class="bi bi-person-circle me-1"></i> Profile
                </a>
            </div>
        </header>
        <div class="p-4">
            <?php include VIEW_PATH . '/components/flash.php'; ?>
            <?= $content ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
