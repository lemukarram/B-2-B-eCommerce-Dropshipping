<?php $pageTitle = 'Global Settings'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-gear-fill text-primary me-2"></i> Platform Configuration</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="/admin/settings">
                    <?php include VIEW_PATH . '/components/csrf_input.php'; ?>

                    <div class="row g-4">
                        <?php
                        $fieldConfig = [
                            'app_name'               => ['label' => 'Application Name',           'type' => 'text', 'icon' => 'bi-window'],
                            'default_delivery_charge' => ['label' => 'Default Delivery Charge (PKR)', 'type' => 'number', 'icon' => 'bi-truck'],
                            'order_number_prefix'     => ['label' => 'Order Number Prefix',        'type' => 'text', 'icon' => 'bi-hash'],
                            'max_bulk_upload_rows'    => ['label' => 'Max Bulk Upload Rows',        'type' => 'number', 'icon' => 'bi-list-ol'],
                            'seller_registration'     => ['label' => 'Seller Registration',         'type' => 'select', 'icon' => 'bi-person-plus',
                                                           'options' => ['open' => 'Open (public can register)', 'closed' => 'Closed']],
                        ];
                        foreach ($fieldConfig as $key => $field):
                            $current = $settings[$key]['value'] ?? '';
                            $desc    = $settings[$key]['description'] ?? '';
                        ?>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark small text-uppercase mb-2">
                                <i class="bi <?= $field['icon'] ?> me-1 text-muted"></i> <?= e($field['label']) ?>
                            </label>
                            <?php if ($field['type'] === 'select'): ?>
                                <select name="<?= $key ?>" class="form-select">
                                    <?php foreach ($field['options'] as $v => $l): ?>
                                        <option value="<?= e($v) ?>" <?= $current === $v ? 'selected' : '' ?>><?= e($l) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="<?= e($field['type']) ?>" name="<?= $key ?>" class="form-control"
                                       value="<?= e($current) ?>"
                                       <?= $field['type'] === 'number' ? 'step="0.01" min="0"' : '' ?>>
                            <?php endif; ?>
                            <?php if ($desc): ?>
                                <div class="form-text small mt-2"><i class="bi bi-info-circle me-1"></i> <?= e($desc) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-5 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-check-circle me-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
