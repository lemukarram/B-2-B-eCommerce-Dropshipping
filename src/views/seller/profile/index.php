<?php $pageTitle = 'My Profile'; ?>
<h4 class="mb-4">Profile & Settings</h4>

<div class="row">
    <!-- Profile form -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Business Details</div>
            <div class="card-body">
                <form method="POST" action="/seller/profile">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               value="<?= e($profile['name']) ?>" required>
                        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name'][0]) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                               value="<?= e($profile['phone']) ?>" required placeholder="03XXXXXXXXX">
                        <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= e($errors['phone'][0]) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" name="business_name" class="form-control"
                               value="<?= e($profile['business_name'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e($profile['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Province</label>
                            <select name="province" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['Punjab','Sindh','KPK','Balochistan','Islamabad','AJK','GB'] as $prov): ?>
                                    <option <?= ($profile['province'] ?? '') === $prov ? 'selected' : '' ?>><?= e($prov) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e($profile['address'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= e($profile['email']) ?>" disabled>
                        <div class="form-text">Email cannot be changed. Contact admin.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Payment Methods</span>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addMethodForm">
                    + Add
                </button>
            </div>

            <!-- Add method form -->
            <div class="collapse" id="addMethodForm">
                <div class="card-body border-bottom">
                    <form method="POST" action="/seller/profile/payment-methods">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <div class="mb-2">
                            <label class="form-label small">Method</label>
                            <select name="method_type" class="form-select form-select-sm" id="methodTypeSelect" onchange="toggleBank()">
                                <option value="bank">Bank Account</option>
                                <option value="easypaisa">Easypaisa</option>
                                <option value="jazzcash">JazzCash</option>
                            </select>
                        </div>
                        <div class="mb-2" id="bankNameField">
                            <label class="form-label small">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="HBL, MCB, Meezan...">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Account Title</label>
                            <input type="text" name="account_title" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Account Number / IBAN</label>
                            <input type="text" name="account_number" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Method</button>
                    </form>
                </div>
            </div>

            <!-- Existing methods list -->
            <?php if (empty($paymentMethods)): ?>
                <div class="card-body text-muted small">No payment methods added yet.</div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($paymentMethods as $m): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-secondary me-1"><?= strtoupper(e($m['method_type'])) ?></span>
                            <?php if ($m['is_primary']): ?><span class="badge bg-primary">Primary</span><?php endif; ?>
                            <div class="fw-medium mt-1"><?= e($m['account_title']) ?></div>
                            <?php if ($m['bank_name']): ?><div class="small text-muted"><?= e($m['bank_name']) ?></div><?php endif; ?>
                            <div class="small font-monospace"><?= e($m['account_number']) ?></div>
                        </div>
                        <div class="d-flex gap-1">
                            <?php if (!$m['is_primary']): ?>
                            <form method="POST" action="/seller/profile/payment-methods/<?= $m['id'] ?>/primary">
                                <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                <button class="btn btn-xs btn-outline-primary" style="font-size:0.75rem;padding:2px 6px;">Set Primary</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="/seller/profile/payment-methods/<?= $m['id'] ?>/delete"
                                  onsubmit="return confirm('Remove this payment method?')">
                                <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                <button class="btn btn-xs btn-outline-danger" style="font-size:0.75rem;padding:2px 6px;">Remove</button>
                            </form>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleBank() {
    const sel = document.getElementById('methodTypeSelect').value;
    document.getElementById('bankNameField').style.display = sel === 'bank' ? '' : 'none';
}
toggleBank();
</script>
