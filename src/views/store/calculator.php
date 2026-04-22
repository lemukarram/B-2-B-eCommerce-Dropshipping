<?php $pageTitle = 'Profit Calculator'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white p-4 border-0">
                <h4 class="mb-1 fw-bold">Dropshipping Profit Calculator</h4>
                <p class="mb-0 opacity-75 small">Estimate your earnings before placing an order.</p>
            </div>
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Product Wholesale Price (Rs.)</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text border-0 bg-white text-muted">Rs.</span>
                                <input type="number" id="wholesale_price" class="form-control border-0" placeholder="0.00" oninput="calculate()">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Your Selling Price (Rs.)</label>
                            <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text border-0 bg-white text-muted">Rs.</span>
                                <input type="number" id="selling_price" class="form-control border-0" placeholder="0.00" oninput="calculate()">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase">Delivery Charge (Rs.)</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" value="<?= number_format(\App\Models\Setting::deliveryCharge(), 0) ?>" readonly>
                            <div class="form-text small">This is the fixed system delivery fee.</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="bg-light h-100 rounded-4 p-4 d-flex flex-column justify-content-center">
                            <div class="text-center mb-4">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Your Net Profit</div>
                                <h1 class="display-4 fw-bold text-primary mb-0" id="result_profit">Rs. 0</h1>
                            </div>
                            
                            <div class="border-top pt-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Profit Margin</span>
                                    <span class="fw-bold" id="result_margin">0%</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Return on Cost</span>
                                    <span class="fw-bold" id="result_roi">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 card border-0 shadow-sm rounded-4 bg-info-subtle">
            <div class="card-body p-4">
                <div class="d-flex gap-3">
                    <i class="bi bi-lightbulb text-info fs-3"></i>
                    <div>
                        <h6 class="fw-bold text-info-emphasis">Success Tip</h6>
                        <p class="text-info-emphasis small mb-0">Most successful stores on EMAG.PK maintain a margin of 15-25% to cover their marketing costs and generate healthy profits.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculate() {
    const wholesale = parseFloat(document.getElementById('wholesale_price').value) || 0;
    const selling = parseFloat(document.getElementById('selling_price').value) || 0;
    
    const profit = selling - wholesale;
    const margin = selling > 0 ? (profit / selling) * 100 : 0;
    const roi = wholesale > 0 ? (profit / wholesale) * 100 : 0;
    
    document.getElementById('result_profit').innerText = 'Rs. ' + Math.round(profit).toLocaleString();
    document.getElementById('result_margin').innerText = margin.toFixed(1) + '%';
    document.getElementById('result_roi').innerText = roi.toFixed(1) + '%';
    
    const profitEl = document.getElementById('result_profit');
    if (profit < 0) {
        profitEl.classList.remove('text-primary');
        profitEl.classList.add('text-danger');
    } else {
        profitEl.classList.remove('text-danger');
        profitEl.classList.add('text-primary');
    }
}
</script>
