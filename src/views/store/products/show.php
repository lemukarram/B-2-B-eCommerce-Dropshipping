<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store/products">Catalog</a></li>
            <li class="breadcrumb-item active"><?= e($product['title']) ?></li>
        </ol>
    </nav>
</div>

<div class="row g-5">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm overflow-hidden rounded-4">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner" style="height: 500px; background: #f8f9fa;">
                    <?php if (empty($images)): ?>
                        <div class="carousel-item active h-100 d-flex align-items-center justify-content-center text-muted">
                            No images available
                        </div>
                    <?php else: ?>
                        <?php foreach ($images as $i => $img): ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?> h-100">
                                <img src="<?= e($img['image_path']) ?>" class="d-block w-100 h-100 object-fit-contain" alt="...">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ps-md-4">
            <h6 class="text-primary fw-bold text-uppercase small mb-2"><?= e($product['category_name']) ?></h6>
            <h1 class="display-6 fw-bold mb-3"><?= e($product['title']) ?></h1>
            <p class="text-muted small mb-4">SKU: <?= e($product['sku']) ?></p>

            <div class="bg-light p-4 rounded-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block">Your Wholesale Price</small>
                        <span class="fs-2 fw-bold text-dark">Rs. <?= number_format((float)$product['wholesale_price'], 2) ?></span>
                    </div>
                    <div class="col-6 ps-4">
                        <small class="text-muted d-block">Availability</small>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $product['stock_quantity'] ?>)</span>
                        <?php else: ?>
                            <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold mb-3">Description</h5>
                <div class="product-description text-muted" style="white-space: pre-line;">
                    <?= nl2br(e($product['description'])) ?>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="/store/orders/create?product_id=<?= $product['id'] ?>" class="btn btn-primary btn-lg py-3 rounded-pill fw-bold <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>">
                    <i class="bi bi-cart-plus me-2"></i> Place Order for This Item
                </a>
                <button type="button" class="btn btn-outline-dark btn-lg py-3 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#marketingAssetsModal">
                    <i class="bi bi-magic me-2"></i> Marketing Assets (AI Prompts)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Marketing Assets Modal -->
<div class="modal fade" id="marketingAssetsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Social Media Marketing Prompts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4">Copy these prompts and paste them into your favorite AI Image Generator (Gemini, Midjourney, DALL-E) to create professional marketing posters for your store.</p>
                
                <?php
                $storeName = $storeProfile['business_name'] ?: $storeProfile['name'];
                $storeLogo = $storeProfile['logo'] ? 'http://' . $_SERVER['HTTP_HOST'] . $storeProfile['logo'] : 'YOUR_STORE_LOGO_URL';
                $productImg = !empty($images) ? 'http://' . $_SERVER['HTTP_HOST'] . $images[0]['image_path'] : '';
                $productName = e($product['title']);
                $productPrice = 'Rs. ' . number_format($product['wholesale_price'] * 1.25, 0); // Suggesting a 25% margin
                $storePhone = e($storeProfile['phone']);
                
                $promptTemplates = [
                    [
                        'ratio' => '1:1 (Square)',
                        'usage' => 'Instagram Post, Facebook Post',
                        'prompt' => "A professional e-commerce product poster for '$productName'. Ratio 1:1. The product image is at $productImg. Style: Modern, clean, and premium. Dominant colors should match the product. Large text: '$productName'. Prominent price tag: '$productPrice'. Include Store Logo: $storeLogo. Brand Name: '$storeName'. Call to action: 'Order Now'. WhatsApp: '$storePhone'. Soft studio lighting, minimalist background."
                    ],
                    [
                        'ratio' => '9:16 (Portrait)',
                        'usage' => 'TikTok, Instagram Reels, Stories',
                        'prompt' => "A high-impact social media story poster for '$productName'. Ratio 9:16. Central focus on the product ($productImg). Vibrant and energetic lifestyle background. Bold typography. Header: 'DEAL OF THE DAY'. Product: '$productName'. Price: '$productPrice'. Bottom section features store name '$storeName' and logo $storeLogo. Mention 'Swipe Up to Order' and WhatsApp: '$storePhone'. 4k resolution, hyper-realistic, professional commercial photography style."
                    ],
                    [
                        'ratio' => '4:3 (Landscape)',
                        'usage' => 'Facebook Ad, Website Banner',
                        'prompt' => "A cinematic wide-angle commercial poster for '$productName'. Ratio 4:3. Wide layout showing the product ($productImg) elegantly placed. Left side features text: '$productName' in luxury font. Below it: 'Premium Quality, Affordable Price'. Price tag: '$productPrice'. Right side features store branding: '$storeName' logo at $storeLogo. Bottom bar with 'Contact for Orders: $storePhone'. Professional product photography, depth of field, soft bokeh, high-end commercial aesthetic."
                    ]
                ];
                ?>

                <div class="row g-3">
                    <?php foreach ($promptTemplates as $i => $pt): ?>
                    <div class="col-12">
                        <div class="card bg-light border-0 shadow-none rounded-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary rounded-pill mb-1"><?= $pt['ratio'] ?></span>
                                        <div class="small fw-bold text-dark"><?= $pt['usage'] ?></div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="copyPrompt(<?= $i ?>)">
                                        <i class="bi bi-copy me-1"></i> Copy
                                    </button>
                                </div>
                                <textarea id="promptText<?= $i ?>" class="form-control form-control-sm bg-white border-0" rows="4" readonly><?= e($pt['prompt']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function copyPrompt(index) {
    const copyText = document.getElementById("promptText" + index);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
    btn.classList.replace('btn-outline-primary', 'btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.replace('btn-success', 'btn-outline-primary');
    }, 2000);
}
</script>

<style>
.object-fit-contain {
    object-fit: contain;
}
.product-description {
    line-height: 1.6;
}
</style>
