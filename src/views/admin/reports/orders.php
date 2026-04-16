<?php $pageTitle = 'Orders Report'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Orders Report</h4>
    <a href="/admin/reports" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<form class="row g-2 mb-4" method="GET" action="/admin/reports/orders">
    <div class="col-auto">
        <label class="form-label">From</label>
        <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom) ?>">
    </div>
    <div class="col-auto">
        <label class="form-label">To</label>
        <input type="date" name="date_to" class="form-control" value="<?= e($dateTo) ?>">
    </div>
    <div class="col-auto d-flex align-items-end">
        <button class="btn btn-primary">Apply</button>
    </div>
</form>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total Orders',          $summary['total_orders'],          'primary'],
        ['Delivered',             $summary['delivered'],             'success'],
        ['Failed',                $summary['failed'],                'danger'],
        ['Returned',              $summary['returned'],              'warning'],
    ];
    foreach ($cards as [$label, $val, $color]):
    ?>
    <div class="col-6 col-md-3">
        <div class="card text-bg-<?= $color ?>">
            <div class="card-body">
                <div class="fs-3 fw-bold"><?= (int)$val ?></div>
                <div class="small"><?= $label ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <?php
    $moneyCards = [
        ['Total Revenue (Selling Price)', $summary['total_revenue'],        'primary'],
        ['Total Platform Cost',           $summary['total_cost'],           'secondary'],
        ['Total Delivery Collected',      $summary['total_delivery'],       'info'],
        ['Total Seller Payouts',          $summary['total_seller_payouts'], 'success'],
    ];
    foreach ($moneyCards as [$label, $val, $color]):
    ?>
    <div class="col-md-3">
        <div class="card border-<?= $color ?>">
            <div class="card-body">
                <div class="text-muted small"><?= $label ?></div>
                <div class="fs-5 fw-bold text-<?= $color ?>">PKR <?= number_format((float)$val, 0) ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
