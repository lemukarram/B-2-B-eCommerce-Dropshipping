<?php $pageTitle = 'Edit Category'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">Edit Category</h5>
                    <small class="text-muted">ID: #<?= $category['id'] ?></small>
                </div>
                <a href="/admin/categories" class="btn btn-light btn-sm border">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/admin/categories/<?= $category['id'] ?>" enctype="multipart/form-data">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                       value="<?= e($category['name']) ?>" required>
                                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name'][0]) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Parent Category</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">— Top Level Category —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php if ($cat['id'] === $category['id']) continue; ?>
                                        <option value="<?= $cat['id'] ?>" <?= $category['parent_id'] == $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <label class="form-label fw-bold small text-uppercase text-muted d-block">Category Image</label>
                            <?php if ($category['image']): ?>
                                <div class="mb-3">
                                    <img src="<?= e($category['image']) ?>" style="width:100px; height:100px; object-fit:cover;" class="rounded-3 shadow-sm border p-1">
                                </div>
                            <?php endif; ?>
                            <div class="border rounded-3 p-2 bg-light">
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= e($category['description']) ?></textarea>
                    </div>

                    <div class="row align-items-center bg-light p-3 rounded-3 mb-4 mx-0">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Display Priority</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($category['sort_order']) ?>" min="0">
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="form-check form-switch ps-5">
                                <input class="form-check-input ms-n5" type="checkbox" name="is_active" value="1" id="catStatus" 
                                       <?= $category['is_active'] ? 'checked' : '' ?> style="width: 3em; height: 1.5em;">
                                <label class="form-check-label fw-bold ms-2" for="catStatus">Active & Visible</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-3">
                            <i class="bi bi-save2-fill me-2"></i> Update Category Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
