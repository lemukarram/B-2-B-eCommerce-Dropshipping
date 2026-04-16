<?php $pageTitle = 'Place New Order'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Place New Order</h4>
    <a href="/seller/orders" class="btn btn-outline-secondary btn-sm">Back to Orders</a>
</div>

<form method="POST" action="/seller/orders" id="orderForm">
    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

    <div class="row">
        <!-- Customer Details -->
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header">Customer Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control"
                               value="<?= e($old['customer_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="customer_phone" placeholder="03XXXXXXXXX"
                               class="form-control" value="<?= e($old['customer_phone'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <textarea name="customer_address" class="form-control" rows="2" required><?= e($old['customer_address'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">City *</label>
                            <input type="text" name="customer_city" class="form-control"
                                   value="<?= e($old['customer_city'] ?? '') ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Province *</label>
                            <select name="customer_province" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach (['Punjab','Sindh','KPK','Balochistan','Islamabad','AJK','GB'] as $p): ?>
                                    <option value="<?= e($p) ?>" <?= ($old['customer_province'] ?? '') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e($old['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Selection -->
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Order Items</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="addItem()">+ Add Item</button>
                </div>
                <div class="card-body">
                    <div id="orderItems">
                        <!-- items injected by JS -->
                    </div>
                    <div id="noItems" class="text-center text-muted py-3">Click "+ Add Item" to add products.</div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Estimated Profit</span>
                        <span id="profitDisplay">PKR 0</span>
                    </div>
                    <div class="text-muted small">Selling Price - Base Price - Delivery Charge (PKR <?= e(\App\Models\Setting::deliveryCharge()) ?>)</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
        </div>
    </div>
</form>

<script>
const products = <?= json_encode(array_map(fn($p) => [
    'id'         => $p['id'],
    'title'      => $p['title'],
    'base_price' => $p['base_price'],
    'sku'        => $p['sku'],
], $products), JSON_HEX_TAG) ?>;

const deliveryCharge = <?= (float)\App\Models\Setting::deliveryCharge() ?>;
let itemCount = 0;

function addItem() {
    document.getElementById('noItems').style.display = 'none';
    const idx = itemCount++;
    const div = document.createElement('div');
    div.className = 'border rounded p-3 mb-3 item-row';
    div.id = 'item_' + idx;
    div.innerHTML = `
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small">Product</label>
                <select name="items[${idx}][product_id]" class="form-select form-select-sm" onchange="updateProfit()" required>
                    <option value="">Select product...</option>
                    ${products.map(p => `<option value="${p.id}" data-base="${p.base_price}">${p.title} (PKR ${p.base_price})</option>`).join('')}
                </select>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small">Qty</label>
                <input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm" value="1" min="1" onchange="updateProfit()">
            </div>
            <div class="col-5 col-md-3">
                <label class="form-label small">Selling Price (PKR)</label>
                <input type="number" name="items[${idx}][selling_price]" class="form-control form-control-sm" placeholder="0.00" step="0.01" min="0" onchange="updateProfit()" required>
            </div>
            <div class="col-3 col-md-2 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(${idx})">Remove</button>
            </div>
        </div>`;
    document.getElementById('orderItems').appendChild(div);
}

function removeItem(idx) {
    const el = document.getElementById('item_' + idx);
    if (el) el.remove();
    if (!document.querySelectorAll('.item-row').length) {
        document.getElementById('noItems').style.display = '';
    }
    updateProfit();
}

function updateProfit() {
    let totalSelling = 0, totalBase = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const select = row.querySelector('select[name*="product_id"]');
        const qty    = parseFloat(row.querySelector('input[name*="quantity"]')?.value) || 0;
        const sell   = parseFloat(row.querySelector('input[name*="selling_price"]')?.value) || 0;
        const base   = parseFloat(select.selectedOptions[0]?.dataset.base) || 0;
        totalSelling += sell * qty;
        totalBase    += base * qty;
    });
    const profit = totalSelling - totalBase - deliveryCharge;
    document.getElementById('profitDisplay').textContent = 'PKR ' + profit.toFixed(2);
    document.getElementById('profitDisplay').className = profit >= 0 ? 'text-success' : 'text-danger';
}
</script>
