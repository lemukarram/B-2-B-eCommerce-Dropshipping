<?php $pageTitle = 'Login'; ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title mb-4 text-center">Seller Login</h4>

                <form method="POST" action="/login" novalidate>
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= e($old['email'] ?? '') ?>" required autocomplete="email">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control" required autocomplete="current-password">
                    </div>

                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label small text-muted">Remember Me</label>
                        </div>
                        <a href="/forgot-password" class="small text-decoration-none">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <hr>
                <p class="text-center mb-0">
                    New seller? <a href="/register">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>
