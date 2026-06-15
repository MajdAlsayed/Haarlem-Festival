<?php
/** @var \App\ViewModels\AdminOrderExportViewModel $viewModel */
$app = $viewModel->appSettings;

$pageTitle = 'Export orders — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <nav class="admin-breadcrumb">
            <a href="/"><?= htmlspecialchars((string)($app['site_name'] ?? 'Festival')) ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/orders">Orders</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Export</span>
        </nav>

        <h1 class="admin-title">Export orders</h1>
        <p class="admin-lead">
            Choose columns and format. <strong>Total amount</strong> and <strong>Paid at</strong> are available as columns.
            Excel downloads as <code>.xls</code> and opens in Microsoft Excel.
        </p>

        <?php if ($viewModel->error !== null): ?>
            <div class="admin-alert admin-alert-error"><?= htmlspecialchars($viewModel->error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/orders/export" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

            <fieldset class="admin-fieldset">
                <legend>Columns</legend>
                <p class="admin-hint">Leave all unchecked to export every column.</p>

                <div class="admin-grid-2">
                    <?php foreach ($viewModel->columnLabels as $key => $label): ?>
                        <label class="admin-checkbox-label">
                            <input type="checkbox" name="columns[]" value="<?= htmlspecialchars($key) ?>">
                            <?= htmlspecialchars($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset">
                <legend>Format</legend>

                <label class="admin-checkbox-label">
                    <input type="radio" name="format" value="csv" checked>
                    CSV (.csv)
                </label>

                <label class="admin-checkbox-label">
                    <input type="radio" name="format" value="excel">
                    Excel (.xls)
                </label>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Download export</button>
                <a href="/admin/orders" class="admin-btn admin-btn-secondary">Back to orders</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>