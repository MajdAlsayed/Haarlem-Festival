<?php
/** @var array $app */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food CMS — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="admin-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Food</span>
        </nav>

        <h1 class="admin-title">Food CMS</h1>
        <p class="admin-lead">Manage restaurants, reservation settings, and page content.</p>

        <section class="admin-cards">
            <a href="/admin/food/restaurants" class="admin-card">
                <span class="admin-card-icon">🍽️</span>
                <h2 class="admin-card-title">Restaurants</h2>
                <p class="admin-card-desc">Add, edit, or remove restaurants shown on the food page.</p>
            </a>
            <a href="/admin/food/settings" class="admin-card">
                <span class="admin-card-icon">⚙️</span>
                <h2 class="admin-card-title">Food settings</h2>
                <p class="admin-card-desc">Hero image, intro text, filters, festival dates, reservation fee.</p>
            </a>
            <a href="/food" class="admin-card" target="_blank" rel="noopener">
                <span class="admin-card-icon">↗</span>
                <h2 class="admin-card-title">View food page</h2>
                <p class="admin-card-desc">Open the public food listing page.</p>
            </a>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>