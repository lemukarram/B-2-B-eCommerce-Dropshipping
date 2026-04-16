<?php $pageTitle = 'My Wallet'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Wallet & Earnings</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-bg-primary">
            <div class="card-body py-4">
                <div class="text-white-50 small text-uppercase fw-bold mb-1">Available Balance</div>
                <div class="fs-2 fw-bold text-white">PKR <?= number_format($wallet['balance'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white">
            <div class="card-body py-4 text-center">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Earned</div>
                <div class="fs-2 fw-bold text-dark">PKR <?= number_format($wallet['total_earned'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-white text-center">
            <div class="card-body py-4">
                <div class="text-muted small text-uppercase fw-bold mb-1">Total Withdrawn</div>
                <div class="fs-2 fw-bold text-dark">PKR <?= number_format($wallet['total_withdrawn'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Transaction History</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">Date</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Description</th>
                            <th class="border-0 text-end">Amount</th>
                            <th class="border-0 text-end pe-4">Balance After</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td class="ps-4"><?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?></td>
                            <td>
                                <?php 
                                $badgeMap = [
                                    'credit'     => 'bg-success-subtle text-success', 
                                    'debit'      => 'bg-danger-subtle text-danger', 
                                    'withdrawal' => 'bg-primary-subtle text-primary', 
                                    'penalty'    => 'bg-danger text-white'
                                ]; 
                                ?>
                                <span class="badge rounded-pill <?= $badgeMap[$tx['type']] ?? 'bg-secondary' ?>">
                                    <?= ucfirst(e($tx['type'])) ?>
                                </span>
                            </td>
                            <td><?= e($tx['description']) ?></td>
                            <td class="text-end fw-bold <?= in_array($tx['type'], ['credit']) ? 'text-success' : 'text-danger' ?>">
                                <?= in_array($tx['type'], ['credit']) ? '+' : '-' ?>PKR <?= number_format($tx['amount'], 2) ?>
                            </td>
                            <td class="text-end pe-4">PKR <?= number_format($tx['balance_after'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-journal-text fs-1 mb-2 d-block opacity-25"></i>
                                No transactions recorded yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
