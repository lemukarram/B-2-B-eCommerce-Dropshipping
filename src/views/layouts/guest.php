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
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <a class="navbar-brand fw-bold fs-4 mb-2 d-inline-block" href="/">EMAG<span class="text-primary">.PK</span></a>
                <p class="text-muted small mb-0">The ultimate B2B dropshipping platform for sellers and stores.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 text-muted">&copy; <?= date('Y') ?> EMAG.PK. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
