<?php $pageTitle = 'Category Markups'; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Add New Markup</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">Set custom pricing rules for specific categories across all your stores.</p>
                
                <form action="/seller/markups" method="POST">
                    <?= \Core\View::component('csrf_input') ?>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <div class="invalid-feedback"><?= e($errors['category_id'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Markup Type</label>
                        <select name="markup_type" class="form-select <?= isset($errors['markup_type']) ? 'is-invalid' : '' ?>" required>
                            <option value="percent">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (Rs.)</option>
                        </select>
                        <?php if (isset($errors['markup_type'])): ?>
                            <div class="invalid-feedback"><?= e($errors['markup_type'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Markup Value</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="markup_value" class="form-control <?= isset($errors['markup_value']) ? 'is-invalid' : '' ?>" placeholder="0.00" required>
                        </div>
                        <?php if (isset($errors['markup_value'])): ?>
                            <div class="invalid-feedback d-block"><?= e($errors['markup_value'][0]) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle me-2"></i> Save Markup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold">Active Markups</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($markups as $markup): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= e($markup['category_name']) ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info border-0 rounded-pill px-3">
                                    <?= ucfirst(e($markup['markup_type'])) ?>
                                </span>
                            </td>
                            <td class="fw-medium">
                                <?= $markup['markup_type'] === 'fixed' ? 'Rs. ' : '' ?>
                                <?= number_format((float)$markup['markup_value'], 2) ?>
                                <?= $markup['markup_type'] === 'percent' ? '%' : '' ?>
                            </td>
                            <td class="text-end">
                                <form action="/seller/markups/<?= e($markup['id']) ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Remove this markup rule?');">
                                    <?= \Core\View::component('csrf_input') ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($markups)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-percent text-muted fs-1 d-block mb-2"></i>
                                <span class="text-muted">No custom markups defined. Base prices will apply.</span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
