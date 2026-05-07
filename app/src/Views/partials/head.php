<?php
// Basic page settings
$pageTitle = $pageTitle ?? ($app['site_name'] ?? 'Haarlem Festival');
$cssVersion = htmlspecialchars((string)($app['css_version'] ?? $appSettings['css_version'] ?? '1'));
$pageStyles = $pageStyles ?? [];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/general.css?v=<?= $cssVersion ?>">
    <link rel="stylesheet" href="/css/style.css?v=<?= $cssVersion ?>">

    <!-- Extra styles for specific pages. -->
    <?php foreach ($pageStyles as $style): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>?v=<?= $cssVersion ?>">
    <?php endforeach; ?>
</head>