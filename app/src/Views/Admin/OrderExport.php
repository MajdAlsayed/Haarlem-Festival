<?php
/** @var \App\ViewModels\AdminOrderExportViewModel $viewModel */
$app = $viewModel->appSettings;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Export orders — <?= htmlspecialchars((string)($app['site_name'] ?? 'Haarlem Festival')) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string)($app['css_version'] ?? '1.0')) ?>">
    <style>
        .admin-export-wrap { max-width: 640px; margin: 2rem auto; padding: 0 1rem; }
        .admin-export-wrap h1 { margin-bottom: 0.5rem; }
        .admin-export-wrap .hint { color: #555; font-size: 0.95rem; margin-bottom: 1.5rem; }
        .admin-export-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1.5rem; margin: 1rem 0; }
        .admin-export-columns label { display: flex; align-items: center; gap: 0.5rem; font-weight: normal; }
        .admin-export-actions { margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; }
        .admin-export-error { background: #fee; border: 1px solid #c00; padding: 0.75rem; margin-bottom: 1rem; border-radius: 4px; }
    </style>
</head>
<body>
<?php $app = $viewModel->appSettings; require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-export-wrap">
    <?php require __DIR__ . '/partials/admin_nav.php'; ?>
    <h1>Export orders</h1>
    <p class="hint">Choose columns and format. <strong>Total amount</strong> and <strong>Paid at</strong> are available as columns. Excel downloads as <code>.xls</code> (opens in Microsoft Excel).</p>

    <?php if ($viewModel->error !== null): ?>
        <div class="admin-export-error"><?= htmlspecialchars($viewModel->error) ?></div>
    <?php endif; ?>

    <form method="post" action="/admin/orders/export">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($viewModel->csrf) ?>">

        <fieldset>
            <legend>Columns</legend>
            <p class="hint">Leave all unchecked to export every column.</p>
            <div class="admin-export-columns">
                <?php foreach ($viewModel->columnLabels as $key => $label): ?>
                    <label>
                        <input type="checkbox" name="columns[]" value="<?= htmlspecialchars($key) ?>">
                        <?= htmlspecialchars($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend>Format</legend>
            <label><input type="radio" name="format" value="csv" checked> CSV (.csv)</label>
            &nbsp;&nbsp;
            <label><input type="radio" name="format" value="excel"> Excel (.xls)</label>
        </fieldset>

        <div class="admin-export-actions">
            <button type="submit" class="btn btn-primary">Download export</button>
            <a href="/">Back to site</a>
        </div>
    </form>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
