<?php $pageTitle = 'Edit Product'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Edit: <?= e($product['title']) ?></h4>
    <a href="/admin/products" class="btn btn-outline-secondary btn-sm">Back to Products</a>
</div>

<form method="POST" action="/admin/products/<?= $product['id'] ?>" enctype="multipart/form-data">
    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">SKU</label>
                        <input type="text" class="form-control" value="<?= e($product['sku']) ?>" disabled>
                        <div class="form-text">SKU cannot be changed after creation.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                               value="<?= e($product['title']) ?>" required>
                        <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title'][0]) ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= e($product['description']) ?></textarea>
                    </div>

                    <!-- Existing images -->
                    <?php if (!empty($images)): ?>
                    <div class="mb-3">
                        <label class="form-label">Current Images</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($images as $img): ?>
                            <div class="position-relative" style="width:100px;">
                                <img src="<?= e($img['image_path']) ?>" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
                                <?php if ($img['is_primary']): ?>
                                    <span class="badge bg-primary position-absolute top-0 start-0 m-1">Primary</span>
                                <?php endif; ?>
                                <form method="POST"
                                      action="/admin/products/<?= $product['id'] ?>/images/<?= $img['id'] ?>/delete"
                                      onsubmit="return confirm('Remove this image?')">
                                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                    <button class="btn btn-danger btn-sm w-100 mt-1">Remove</button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Add Images</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/jpeg,image/png,image/webp">
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
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base Price (PKR) <span class="text-danger">*</span></label>
                        <input type="number" name="base_price" class="form-control"
                               value="<?= e($product['base_price']) ?>" step="0.01" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control"
                               value="<?= e($product['stock_quantity']) ?>" min="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   <?= $product['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</form>
