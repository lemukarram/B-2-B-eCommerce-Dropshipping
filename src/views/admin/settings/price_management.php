<?php $pageTitle = 'Price Management'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Margin Rules & Price Synchronization</h6>
                <form action="/admin/settings/price-sync" method="POST">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                    <button type="submit" class="btn btn-light btn-sm fw-bold shadow-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Whole System Prices
                    </button>
                </form>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Prices are calculated from <strong>Buying Price (CZ Price)</strong> to <strong>Wholesale Price (Base Price)</strong>. 
                    Rules are applied in order of priority: <strong>Price Range > Category Margin > Overall Store Margin</strong>.
                </p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Target / Range</th>
                                <th>Margin Type</th>
                                <th>Value</th>
                                <th>Max Cap</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= ucfirst($rule['rule_type']) ?></span></td>
                                    <td>
                                        <?php if ($rule['rule_type'] === 'category'): ?>
                                            <?= e($rule['category_name']) ?>
                                        <?php elseif ($rule['rule_type'] === 'range'): ?>
                                            <?= (float)$rule['min_price'] ?> - <?= (float)$rule['max_price'] ?>
                                        <?php else: ?>
                                            All Store
                                        <?php endif; ?>
                                    </td>
                                    <td><?= ucfirst(str_replace('_', ' ', $rule['margin_type'])) ?></td>
                                    <td><?= $rule['margin_type'] === 'fixed' ? 'PKR ' : '' ?><?= (float)$rule['margin_value'] ?><?= str_contains($rule['margin_type'], 'percent') ? '%' : '' ?></td>
                                    <td><?= $rule['max_cap'] ? 'PKR ' . (float)$rule['max_cap'] : '-' ?></td>
                                    <td>
                                        <form action="/admin/settings/price-rules/<?= $rule['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this rule?');">
                                            <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                                            <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($rules)): ?>
                                <tr><td colspan="6" class="text-center py-4">No margin rules defined.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h6 class="mb-0">Add New Margin Rule</h6>
            </div>
            <div class="card-body">
                <form action="/admin/settings/price-rules" method="POST" class="row g-3">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Rule Type</label>
                        <select name="rule_type" id="rule_type" class="form-select form-select-sm" required>
                            <option value="overall">Overall Store</option>
                            <option value="category">Category Wise</option>
                            <option value="range">Price Range</option>
                        </select>
                    </div>

                    <div id="category_select" class="col-md-3 d-none">
                        <label class="form-label small fw-bold">Select Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="range_select" class="col-md-3 d-none">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Min Price</label>
                                <input type="number" name="min_price" class="form-control form-control-sm" step="0.01">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Max Price</label>
                                <input type="number" name="max_price" class="form-control form-control-sm" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Margin Type</label>
                        <select name="margin_type" class="form-select form-select-sm" required>
                            <option value="fixed">Fixed Amount</option>
                            <option value="percent">Percentage (%)</option>
                            <option value="percent_cap">Percentage with Max Cap</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Margin Value</label>
                        <input type="number" name="margin_value" class="form-control form-control-sm" step="0.01" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Max Cap (Optional)</label>
                        <input type="number" name="max_cap" class="form-control form-control-sm" step="0.01">
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('rule_type').addEventListener('change', function() {
    const val = this.value;
    document.getElementById('category_select').classList.toggle('d-none', val !== 'category');
    document.getElementById('range_select').classList.toggle('d-none', val !== 'range');
});
</script>
