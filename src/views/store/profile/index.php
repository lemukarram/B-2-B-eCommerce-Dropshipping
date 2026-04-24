<?php $pageTitle = 'Profile & Store Settings'; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Store Information</h5>
            </div>
            <div class="card-body">
                <form action="/store/profile" method="POST" enctype="multipart/form-data">
                    <?= csrf_input() ?>
                    <input type="hidden" name="existing_logo" value="<?= e($profile['logo'] ?? '') ?>">
                    
                    <div class="row mb-4 align-items-center">
                        <div class="col-auto">
                            <div class="position-relative">
                                <?php if ($profile['logo']): ?>
                                    <img src="<?= e($profile['logo']) ?>" class="rounded-circle border" style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 100px; height: 100px;">
                                        <i class="bi bi-shop fs-1 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label small fw-bold text-muted d-block">Store Logo</label>
                            <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                            <div class="form-text small">Recommended: Square image, 512x512px.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Owner Name</label>
                            <input type="text" name="name" class="form-control" value="<?= e($profile['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Store/Business Name</label>
                            <input type="text" name="business_name" class="form-control" value="<?= e($profile['business_name'] ?? '') ?>" required placeholder="e.g. My Amazing Store">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" class="form-control bg-light" value="<?= e($profile['email']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?= e($profile['phone']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Business Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e($profile['address'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e($profile['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Province</label>
                            <input type="text" name="province" class="form-control" value="<?= e($profile['province'] ?? '') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Payment Methods</h5>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addMethodModal">
                    <i class="bi bi-plus-lg me-1"></i> Add New
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small">Your profits will be sent to your primary payment method.</p>
                
                <div class="list-group list-group-flush mt-3">
                    <?php foreach($paymentMethods as $m): ?>
                        <div class="list-group-item px-0 py-3 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark text-capitalize">
                                        <?= e($m['method_type']) ?> 
                                        <?php if($m['bank_name']): ?>- <span class="text-muted small"><?= e($m['bank_name']) ?></span><?php endif; ?>
                                    </div>
                                    <div class="small text-muted"><?= e($m['account_title']) ?></div>
                                    <div class="fw-medium"><?= e($m['account_number']) ?></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <form action="/store/payment-methods/<?= $m['id'] ?>/delete" method="POST" onsubmit="return confirm('Remove this payment method?')">
                                        <?= csrf_input() ?>
                                        <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($paymentMethods)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-bank text-muted fs-1 d-block mb-2"></i>
                            <span class="text-muted small">No payment methods added.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/store/payment-methods" method="POST">
                <?= csrf_input() ?>
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Method Type</label>
                        <select name="method_type" id="method_type" class="form-select" required onchange="toggleBankFields()">
                            <option value="bank">Bank Account</option>
                            <option value="easypaisa">EasyPaisa</option>
                            <option value="jazzcash">JazzCash</option>
                        </select>
                    </div>
                    <div id="bank_fields" class="mb-3">
                        <label class="form-label small fw-bold">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. Allied Bank, HBL">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Account Title</label>
                        <input type="text" name="account_title" class="form-control" required placeholder="Name on account">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Account / IBAN / Phone</label>
                        <input type="text" name="account_number" class="form-control" required placeholder="Account or wallet number">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleBankFields() {
    const type = document.getElementById('method_type').value;
    document.getElementById('bank_fields').style.display = (type === 'bank') ? 'block' : 'none';
}

/**
 * FIX: Modal Backdrop issues.
 * Move the modal to document.body to escape the 'animate-fade-in' stacking context.
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addMethodModal');
    if (modal) {
        document.body.appendChild(modal);
    }
});
</script>
