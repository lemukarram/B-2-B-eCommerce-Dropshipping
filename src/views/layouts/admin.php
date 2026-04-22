<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin Panel') ?> — EMAG.PK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="d-flex">
    <div class="sidebar p-3 d-flex flex-column" style="width: 260px; flex-shrink: 0;">
        <a class="navbar-brand fw-bold text-white fs-4 mb-4 px-3" href="/admin">
            EMAG<span class="text-primary">.PK</span>
            <span class="badge bg-danger fs-6 align-middle ms-1" style="font-size: 0.6rem !important;">Admin</span>
        </a>
        
        <div class="nav flex-column flex-grow-1">
            <small class="text-uppercase text-muted fw-bold mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Main</small>
            <a class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/admin') && strlen($_SERVER['REQUEST_URI']) <= 7 ? 'active' : '' ?>" href="/admin">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>

            <small class="text-uppercase text-muted fw-bold mt-4 mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Catalogue</small>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/categories') ? 'active' : '' ?>" href="/admin/categories">
                <i class="bi bi-tags-fill"></i> Categories
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/products') && !str_contains($_SERVER['REQUEST_URI'], '/bulk-upload') ? 'active' : '' ?>" href="/admin/products">
                <i class="bi bi-box-seam-fill"></i> Products
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/products/bulk-upload') ? 'active' : '' ?>" href="/admin/products/bulk-upload">
                <i class="bi bi-cloud-arrow-up-fill"></i> Bulk Upload
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/products/cz-import') ? 'active' : '' ?>" href="/admin/products/cz-import">
                <i class="bi bi-file-earmark-excel-fill text-info"></i> CZ Import
            </a>

            <small class="text-uppercase text-muted fw-bold mt-4 mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Commerce</small>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/orders') ? 'active' : '' ?>" href="/admin/orders">
                <i class="bi bi-cart-fill"></i> Orders
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/payments') ? 'active' : '' ?>" href="/admin/payments">
                <i class="bi bi-currency-dollar"></i> Payments
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/sellers') ? 'active' : '' ?>" href="/admin/sellers">
                <i class="bi bi-people-fill"></i> Sellers
            </a>

            <small class="text-uppercase text-muted fw-bold mt-4 mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Analytics</small>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/reports') ? 'active' : '' ?>" href="/admin/reports">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>

            <small class="text-uppercase text-muted fw-bold mt-4 mb-2 px-3" style="font-size: 0.7rem; letter-spacing: 0.05rem;">Config</small>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/settings/price-management') ? 'active' : '' ?>" href="/admin/settings/price-management">
                <i class="bi bi-currency-exchange text-warning"></i> Price Management
            </a>
            <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/admin/settings') && !str_contains($_SERVER['REQUEST_URI'], '/price-management') ? 'active' : '' ?>" href="/admin/settings">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </div>

        <div class="mt-auto px-3 py-3 border-top border-secondary">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary rounded-circle p-2 me-2">
                    <i class="bi bi-person-fill text-white"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-white text-truncate fw-medium small"><?= e(\Core\Auth::name()) ?></div>
                    <div class="text-muted text-truncate" style="font-size: 0.75rem;">Administrator</div>
                </div>
            </div>
            <a href="/logout" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
        </div>
    </div>

    <main class="flex-grow-1" style="min-width:0; background: var(--bg-light); min-height: 100vh;">
        <header class="bg-white border-bottom px-4 py-3 sticky-top">
            <h5 class="mb-0 fw-bold"><?= e($pageTitle ?? 'Dashboard') ?></h5>
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
