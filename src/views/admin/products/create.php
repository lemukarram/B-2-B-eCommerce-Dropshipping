<?php $pageTitle = 'New Product'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>New Product</h4>
    <a href="/admin/products" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<form method="POST" action="/admin/products" enctype="multipart/form-data">
    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control <?= isset($errors['sku']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['sku'] ?? '') ?>" required>
                            <?php if (isset($errors['sku'])): ?><div class="invalid-feedback"><?= e($errors['sku'][0]) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($old['title'] ?? '') ?>" required>
                            <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title'][0]) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">First image will be set as primary. Max 10 MB each.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">— Uncategorized —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base Price (PKR) <span class="text-danger">*</span></label>
                        <input type="number" name="base_price" class="form-control <?= isset($errors['base_price']) ? 'is-invalid' : '' ?>"
                               value="<?= e($old['base_price'] ?? '') ?>" step="0.01" min="0" required>
                        <?php if (isset($errors['base_price'])): ?><div class="invalid-feedback"><?= e($errors['base_price'][0]) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control"
                               value="<?= e($old['stock_quantity'] ?? 0) ?>" min="0" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   <?= ($old['is_active'] ?? '1') ? 'checked' : '' ?>>
                            <label class="form-check-label">Active (visible to sellers)</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Product</button>
                </div>
            </div>
        </div>
    </div>
</form>
