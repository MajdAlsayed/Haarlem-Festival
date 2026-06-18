<?php

$app = $viewModel->appSettings;
$h = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'Export orders — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page admin-orders-page admin-export-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/orders">Orders</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Export</span>
        </nav>

        <section class="admin-page-header admin-page-header-row">
            <div>
                <h1 class="admin-title">Export orders</h1>
                <p class="admin-subtitle">Download order data for reporting or analysis.</p>
            </div>
            <a href="/admin/orders" class="admin-btn admin-btn-secondary">← Back to orders</a>
        </section>

        <?php if ($viewModel->error !== null): ?>
            <div class="admin-alert admin-alert-error"><?= $h($viewModel->error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/orders/export" class="admin-export-form">
            <input type="hidden" name="_csrf" value="<?= $h($viewModel->csrf) ?>">

            <div class="admin-export-layout">
                <section class="admin-panel admin-export-card">
                    <div class="admin-panel-top admin-export-card-head">
                        <div>
                            <h2 class="admin-section-heading">Columns</h2>
                            <p class="admin-section-note">Pick fields to include. Leave all unchecked for every column.</p>
                        </div>
                        <div class="admin-export-quick-actions">
                            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-export-select-all>Select all</button>
                            <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" data-export-clear-all>Clear</button>
                        </div>
                    </div>
                    <div class="admin-export-columns">
                        <?php foreach ($viewModel->columnLabels as $key => $label): ?>
                            <label class="admin-export-column-card">
                                <input type="checkbox" name="columns[]" value="<?= $h($key) ?>" data-export-column>
                                <span class="admin-export-column-title"><?= $h($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </section>

                <aside class="admin-export-sidebar">
                    <section class="admin-panel admin-export-card">
                        <div class="admin-panel-top">
                            <h2 class="admin-section-heading">Format</h2>
                        </div>
                        <div class="admin-format-cards">
                            <label class="admin-format-card is-selected">
                                <input type="radio" name="format" value="csv" checked>
                                <span class="admin-format-card-title">CSV</span>
                                <span class="admin-format-card-desc">Spreadsheets &amp; imports</span>
                                <span class="admin-format-card-ext">.csv</span>
                            </label>
                            <label class="admin-format-card">
                                <input type="radio" name="format" value="excel">
                                <span class="admin-format-card-title">Excel</span>
                                <span class="admin-format-card-desc">Opens in Microsoft Excel</span>
                                <span class="admin-format-card-ext">.xls</span>
                            </label>
                        </div>
                    </section>

                    <section class="admin-panel admin-export-card admin-export-download-card">
                        <p class="admin-export-download-lead">Ready to download your export file.</p>
                        <button type="submit" class="admin-btn admin-btn-primary admin-btn-export admin-btn-export--block">
                            <span class="admin-btn-icon" aria-hidden="true">↓</span>
                            Download export
                        </button>
                    </section>
                </aside>
            </div>
        </form>
    </div>
</main>

<script>
(function () {
    const form = document.querySelector('.admin-export-form');
    if (!form) return;

    const boxes = form.querySelectorAll('[data-export-column]');
    form.querySelector('[data-export-select-all]')?.addEventListener('click', function () {
        boxes.forEach(function (el) { el.checked = true; });
    });
    form.querySelector('[data-export-clear-all]')?.addEventListener('click', function () {
        boxes.forEach(function (el) { el.checked = false; });
    });

    form.querySelectorAll('.admin-format-card input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            form.querySelectorAll('.admin-format-card').forEach(function (card) {
                card.classList.toggle('is-selected', card.querySelector('input')?.checked === true);
            });
        });
    });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
