<?php
/**
 * Bootstrap 5 pagination component.
 *
 * Variables expected:
 *   $pagination = ['current_page' => int, 'last_page' => int, 'total' => int, 'per_page' => int]
 *   $baseUrl    = string (e.g. '/admin/products')
 *   $queryExtra = string (e.g. '&status=pending') — additional query params
 */
$queryExtra = $queryExtra ?? '';
?>
<?php if ($pagination['last_page'] > 1): ?>
<nav aria-label="Pagination">
    <ul class="pagination">
        <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($baseUrl . '?page=' . ($pagination['current_page'] - 1) . $queryExtra) ?>">Previous</a>
        </li>

        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
            <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($baseUrl . '?page=' . $i . $queryExtra) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?= $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= e($baseUrl . '?page=' . ($pagination['current_page'] + 1) . $queryExtra) ?>">Next</a>
        </li>
    </ul>
</nav>
<p class="text-muted small">
    Showing <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>–<?= min($pagination['total'], $pagination['current_page'] * $pagination['per_page']) ?> of <?= $pagination['total'] ?> results
</p>
<?php endif; ?>
