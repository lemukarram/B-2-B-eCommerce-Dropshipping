<?php $pageTitle = 'New Category'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Create New Category</h5>
                <a href="/admin/categories" class="btn btn-light btn-sm border">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/admin/categories" enctype="multipart/form-data">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                       value="<?= e($old['name'] ?? '') ?>" placeholder="e.g. Electronics" required>
                                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name'][0]) ?></div><?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Parent Category</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">— Top Level Category —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($old['parent_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Choose a parent if this is a sub-category.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="mb-4">
                                <label class="form-label fw-bold small text-uppercase text-muted">Category Image</label>
                                <div class="border rounded-3 p-3 text-center bg-light">
                                    <i class="bi bi-cloud-arrow-up fs-1 text-muted d-block mb-2"></i>
                                    <input type="file" name="image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-muted">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Briefly describe what this category contains..."><?= e($old['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row align-items-center bg-light p-3 rounded-3 mb-4 mx-0">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Display Priority</label>
                            <input type="number" name="sort_order" class="form-control" value="<?= e($old['sort_order'] ?? 0) ?>" min="0">
                            <div class="form-text">Lower numbers appear first.</div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="form-check form-switch ps-5">
                                <input class="form-check-input ms-n5" type="checkbox" name="is_active" value="1" id="catStatus" checked style="width: 3em; height: 1.5em;">
                                <label class="form-check-label fw-bold ms-2" for="catStatus">Active & Visible</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-3">
                            <i class="bi bi-check-circle-fill me-2"></i> Create System Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
