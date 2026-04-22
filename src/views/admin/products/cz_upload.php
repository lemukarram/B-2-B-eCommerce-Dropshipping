<?php $pageTitle = 'CZ Product Import'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>CZ Product Import (Excel)</h4>
    <a href="/admin/products" class="btn btn-outline-secondary btn-sm">Back to Products</a>
</div>

<div class="row">
    <div class="col-md-7">
        <?php if (!empty($pending)): ?>
            <div class="card border-warning mb-4">
                <div class="card-header bg-warning text-dark">Category Not Found</div>
                <div class="card-body">
                    <p>The file <strong><?= e($pending['file']) ?></strong> is for category reference <strong><?= e($pending['reference']) ?></strong>, but this reference does not exist in the database.</p>
                    <form method="POST" action="/admin/products/cz-import">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <input type="hidden" name="pending_file" value="<?= e($pending['file']) ?>">
                        <input type="hidden" name="reference" value="<?= e($pending['reference']) ?>">
                        
                        <div class="mb-3">
                            <label for="category_name" class="form-label">New Category Name</label>
                            <input type="text" name="category_name" id="category_name" class="form-control" required placeholder="e.g. Electronics, Clothing">
                            <div class="form-text">This will create a new category and proceed with the import.</div>
                        </div>
                        <button type="submit" class="btn btn-warning">Create Category & Import</button>
                        <a href="/admin/products/cz-import" class="btn btn-link text-muted">Cancel</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Upload CZ Excel File</h6>
                    <p class="text-muted small">
                        Filename should be the Category Reference (e.g. <code>34.xlsx</code>).<br>
                        Columns required: <code>pid</code>, <code>name</code>, <code>price</code>, <code>description</code>, <code>image name</code>.
                    </p>

                    <form method="POST" action="/admin/products/cz-import" enctype="multipart/form-data">
                        <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                        <div class="mb-3">
                            <label for="cz_file" class="form-label">Select File (.xlsx)</label>
                            <input type="file" name="cz_file" id="cz_file" class="form-control" accept=".xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Process Import</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-5">
        <div class="card border-info">
            <div class="card-header bg-info text-white">System Logic</div>
            <div class="card-body small">
                <ul>
                    <li><strong>Sync:</strong> Uses <code>pid</code> to check for existing products.</li>
                    <li><strong>Images:</strong> Automatically maps <code>image name</code> to the product (stored in <code>uploads/products/</code>).</li>
                    <li><strong>Safety:</strong> Handles special characters in descriptions using UTF-8 conversion.</li>
                    <li><strong>Automation:</strong> SKU is auto-generated as <code>CZ-[pid]</code>.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($result) && !isset($result['missing_category'])): ?>
<div class="card mt-4">
    <div class="card-header">Import Results</div>
    <div class="card-body">
        <?php if (!empty($result['errors'])): ?>
            <div class="alert alert-danger">
                <strong>Errors encountered:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($result['errors'] as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                Successfully processed.
                <ul>
                    <li>New Products: <strong><?= (int)$result['inserted'] ?></strong></li>
                    <li>Updated Products: <strong><?= (int)$result['updated'] ?></strong></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
