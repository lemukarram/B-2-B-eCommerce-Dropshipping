<?php $pageTitle = 'Register as Seller'; ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="card-title mb-1">Become a Seller</h4>
                <p class="text-muted mb-4">After registration, your account will be reviewed by admin before activation.</p>

                <form method="POST" action="/register" novalidate>
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name'][0]) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" placeholder="03XXXXXXXXX"
                                   class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['phone'] ?? '') ?>" required>
                            <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= e($errors['phone'][0]) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                               class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= e($old['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email'][0]) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" name="business_name"
                               class="form-control <?= isset($errors['business_name']) ? 'is-invalid' : '' ?>"
                               value="<?= e($old['business_name'] ?? '') ?>" required>
                        <?php if (isset($errors['business_name'])): ?><div class="invalid-feedback"><?= e($errors['business_name'][0]) ?></div><?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            <?php if (isset($errors['password'])): ?><div class="text-danger small mt-1"><?= e($errors['password'][0]) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>

                <hr>
                <p class="text-center mb-0">Already have an account? <a href="/login">Login</a></p>
            </div>
        </div>
    </div>
</div>
