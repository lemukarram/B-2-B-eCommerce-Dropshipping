<?php $pageTitle = 'Bulk Product Upload'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Bulk Product Upload</h4>
    <a href="/admin/products" class="btn btn-outline-secondary btn-sm">Back to Products</a>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Upload CSV or XLSX File</h6>
                <p class="text-muted small">
                    Required columns: <code>sku</code>, <code>title</code>, <code>base_price</code><br>
                    Optional columns: <code>category_slug</code>, <code>stock_quantity</code>, <code>description</code>
                </p>

                <form method="POST" action="/admin/products/bulk-upload" enctype="multipart/form-data">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                    <div class="mb-3">
                        <label for="bulk_file" class="form-label">Select File</label>
                        <input type="file" name="bulk_file" id="bulk_file"
                               class="form-control" accept=".csv,.xls,.xlsx" required>
                        <div class="form-text">Max 10 MB. Up to 500 rows per upload.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload & Import</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header">CSV Template</div>
            <div class="card-body">
                <pre class="small mb-0">sku,title,category_slug,base_price,stock_quantity,description
PROD-001,Sample Product,electronics,250.00,100,Description here
PROD-002,Another Product,,150.00,50,</pre>
                <a href="#" class="btn btn-sm btn-outline-secondary mt-2">Download Template</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($result)): ?>
<div class="card mt-4">
    <div class="card-header">Import Result</div>
    <div class="card-body">
        <?php if (!empty($result['errors'])): ?>
            <div class="alert alert-danger">
                <strong>Import failed. Fix the following errors and re-upload:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($result['errors'] as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                Successfully imported <strong><?= (int)$result['inserted'] ?></strong> products.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
