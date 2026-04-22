<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="d-flex align-items-center mb-4">
            <a href="/store/products" class="btn btn-light rounded-circle me-3"><i class="bi bi-arrow-left"></i></a>
            <h2 class="fw-bold mb-0">Place Dropshipping Order</h2>
        </div>

        <form action="/store/orders" method="POST" id="orderForm">
            <?= csrf_input() ?>
            
            <div class="row g-4">
                <!-- Customer Details -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold">Customer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Full Name</label>
                                <input type="text" name="customer_name" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?= isset($errors['customer_name']) ? 'is-invalid' : '' ?>" value="<?= e($old['customer_name'] ?? '') ?>" required>
                                <?php if(isset($errors['customer_name'])): ?><div class="invalid-feedback"><?= $errors['customer_name'][0] ?></div><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Phone Number</label>
                                <input type="text" name="customer_phone" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?= isset($errors['customer_phone']) ? 'is-invalid' : '' ?>" placeholder="03xxxxxxxxx" value="<?= e($old['customer_phone'] ?? '') ?>" required>
                                <?php if(isset($errors['customer_phone'])): ?><div class="invalid-feedback"><?= $errors['customer_phone'][0] ?></div><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Shipping Address</label>
                                <textarea name="customer_address" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?= isset($errors['customer_address']) ? 'is-invalid' : '' ?>" rows="3" required><?= e($old['customer_address'] ?? '') ?></textarea>
                                <?php if(isset($errors['customer_address'])): ?><div class="invalid-feedback"><?= $errors['customer_address'][0] ?></div><?php endif; ?>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label class="form-label text-muted small fw-bold text-uppercase">City</label>
                                    <input type="text" name="customer_city" class="form-control bg-light border-0 py-2 px-3 rounded-3 <?= isset($errors['customer_city']) ? 'is-invalid' : '' ?>" value="<?= e($old['customer_city'] ?? '') ?>" required>
                                    <?php if(isset($errors['customer_city'])): ?><div class="invalid-feedback"><?= $errors['customer_city'][0] ?></div><?php endif; ?>
                                </div>
                                <div class="col">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Province</label>
                                    <select name="customer_province" class="form-select bg-light border-0 py-2 px-3 rounded-3 <?= isset($errors['customer_province']) ? 'is-invalid' : '' ?>" required>
                                        <option value="">Select</option>
                                        <?php foreach(['Punjab','Sindh','KPK','Balochistan','ICT','Gilgit','AJK'] as $p): ?>
                                            <option value="<?= $p ?>" <?= ($old['customer_province'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if(isset($errors['customer_province'])): ?><div class="invalid-feedback"><?= $errors['customer_province'][0] ?></div><?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label text-muted small fw-bold text-uppercase">Order Notes (Optional)</label>
                                <textarea name="notes" class="form-control bg-light border-0 py-2 px-3 rounded-3" rows="2"><?= e($old['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Selection -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Order Items</h5>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="addItem()">
                                <i class="bi bi-plus-lg me-1"></i> Add Product
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="itemsContainer">
                                <!-- Dynamic items here -->
                            </div>
                            
                            <?php if(isset($errors['items'])): ?>
                                <div class="alert alert-danger py-2 small rounded-3 mt-3"><?= $errors['items'][0] ?></div>
                            <?php endif; ?>

                            <div class="bg-light p-4 rounded-4 mt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Wholesale Subtotal</span>
                                    <span class="fw-bold" id="wholesaleTotal">Rs. 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Delivery Charge</span>
                                    <span class="fw-bold">Rs. <?= number_format(\App\Models\Setting::deliveryCharge(), 2) ?></span>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between mb-0">
                                    <span class="fs-5 fw-bold">Your Potential Profit</span>
                                    <span class="fs-5 fw-bold text-success" id="potentialProfit">Rs. 0.00</span>
                                </div>
                                <div class="small text-muted mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Profit = (Total Selling - Total Wholesale)
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 p-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold">
                                Confirm & Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Item Template -->
<template id="itemTemplate">
    <div class="item-row bg-light p-3 rounded-4 mb-3 border position-relative overflow-hidden">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 small remove-btn" onclick="removeItem(this)"></button>
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <div class="bg-white rounded-3 shadow-sm d-flex align-items-center justify-content-center overflow-hidden" style="width: 70px; height: 70px;">
                    <img src="" class="item-img w-100 h-100 object-fit-contain d-none">
                    <i class="bi bi-image text-muted item-img-placeholder"></i>
                </div>
            </div>
            <div class="col">
                <label class="form-label text-muted small fw-bold text-uppercase">Select Product</label>
                <select name="items[INDEX][product_id]" class="form-select product-select border-0 bg-white shadow-sm rounded-3" required onchange="updateRowPrices(this)">
                    <option value="" data-price="0" data-img="">-- Choose Product --</option>
                    <?php foreach($products as $p): ?>
                        <option value="<?= $p['id'] ?>" data-price="<?= $p['wholesale_price'] ?>" data-img="<?= e($p['image_path'] ?? '') ?>" <?= (int)($_GET['product_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= e($p['title']) ?> (Wholesale: Rs. <?= number_format($p['wholesale_price'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row g-2 mt-2">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Qty</label>
                <input type="number" name="items[INDEX][quantity]" class="form-control border-0 bg-white shadow-sm rounded-3 qty-input" value="1" min="1" required oninput="calculateTotals()">
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold text-uppercase">Wholesale (Each)</label>
                <input type="text" class="form-control border-0 bg-white shadow-sm rounded-3 wholesale-display" readonly value="0.00">
            </div>
            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold text-uppercase">Selling (Each)</label>
                <div class="input-group shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text border-0 bg-white">Rs.</span>
                    <input type="number" name="items[INDEX][selling_price]" class="form-control border-0 bg-white selling-input" step="0.01" placeholder="Enter your price" required oninput="calculateTotals()">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
let itemIndex = 0;
const deliveryCharge = <?= \App\Models\Setting::deliveryCharge() ?>;

function addItem() {
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('itemTemplate').innerHTML;
    const html = template.replace(/INDEX/g, itemIndex);
    
    const div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    
    // If it was added with a pre-selected product (from URL), trigger update
    const select = container.lastElementChild.querySelector('.product-select');
    if (select.value) {
        updateRowPrices(select);
    }
    
    itemIndex++;
    calculateTotals();
}

function removeItem(btn) {
    btn.closest('.item-row').remove();
    calculateTotals();
}

function updateRowPrices(select) {
    const row = select.closest('.item-row');
    const option = select.options[select.selectedIndex];
    const wholesale = parseFloat(option.getAttribute('data-price')) || 0;
    const imgPath = option.getAttribute('data-img');
    
    row.querySelector('.wholesale-display').value = wholesale.toFixed(2);
    
    // Update Image
    const imgEl = row.querySelector('.item-img');
    const placeholderEl = row.querySelector('.item-img-placeholder');
    if (imgPath) {
        imgEl.src = imgPath;
        imgEl.classList.remove('d-none');
        placeholderEl.classList.add('d-none');
    } else {
        imgEl.classList.add('d-none');
        placeholderEl.classList.remove('d-none');
    }
    
    // Auto-fill selling price if empty (wholesale + 20% margin as default suggestion)
    const sellingInput = row.querySelector('.selling-input');
    if (!sellingInput.value || sellingInput.value == 0) {
        sellingInput.value = Math.ceil(wholesale * 1.2 / 5) * 5; // Round to nearest 5
    }
    
    calculateTotals();
}

function calculateTotals() {
    let totalWholesale = 0;
    let totalSelling = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        const wholesale = parseFloat(row.querySelector('.wholesale-display').value) || 0;
        const selling = parseFloat(row.querySelector('.selling-input').value) || 0;
        
        totalWholesale += (wholesale * qty);
        totalSelling += (selling * qty);
    });
    
    document.getElementById('wholesaleTotal').innerText = 'Rs. ' + totalWholesale.toLocaleString(undefined, {minimumFractionDigits: 2});
    
    const profit = totalSelling - totalWholesale;
    const profitEl = document.getElementById('potentialProfit');
    profitEl.innerText = 'Rs. ' + profit.toLocaleString(undefined, {minimumFractionDigits: 2});
    
    if (profit < 0) {
        profitEl.classList.remove('text-success');
        profitEl.classList.add('text-danger');
    } else {
        profitEl.classList.remove('text-danger');
        profitEl.classList.add('text-success');
    }
}

// Add first item on load, or re-populate old items
window.onload = function() {
    <?php if (isset($old['items']) && is_array($old['items'])): ?>
        <?php foreach ($old['items'] as $index => $item): ?>
            addItem();
            const lastRow = document.getElementById('itemsContainer').lastElementChild;
            const select = lastRow.querySelector('.product-select');
            select.value = "<?= $item['product_id'] ?>";
            lastRow.querySelector('.qty-input').value = "<?= $item['quantity'] ?>";
            lastRow.querySelector('.selling-input').value = "<?= $item['selling_price'] ?>";
            updateRowPrices(select);
        <?php endforeach; ?>
    <?php else: ?>
        addItem();
    <?php endif; ?>
};
</script>

<style>
.item-row { transition: all 0.2s; border-color: transparent !important; }
.item-row:hover { border-color: #dee2e6 !important; }
.remove-btn { display: none; }
.item-row:hover .remove-btn { display: block; }
</style>
