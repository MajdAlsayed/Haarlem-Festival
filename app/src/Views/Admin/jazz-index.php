<?php
/** @var array $app */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jazz CMS — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Jazz</span>
        </nav>

        <h1 class="admin-title">Jazz CMS</h1>
        <p class="admin-lead">Manage jazz events, homepage layout, artist pages, and discography.</p>

        <section class="admin-cards">
            <a href="/admin/jazz/events" class="admin-card">
                <span class="admin-card-icon">🎷</span>
                <h2 class="admin-card-title">Events</h2>
                <p class="admin-card-desc">Schedule, venues, descriptions, preview audio.</p>
            </a>
            <a href="/admin/jazz/settings" class="admin-card">
                <span class="admin-card-icon">🎛️</span>
                <h2 class="admin-card-title">Layout &amp; images</h2>
                <p class="admin-card-desc">Hero, card images, day order, artist page copy.</p>
            </a>
            <a href="/admin/jazz/discography" class="admin-card">
                <span class="admin-card-icon">💿</span>
                <h2 class="admin-card-title">Discography</h2>
                <p class="admin-card-desc">Tracks per artist (e.g. Karsu).</p>
            </a>
            <a href="/jazz" class="admin-card" target="_blank" rel="noopener">
                <span class="admin-card-icon">↗</span>
                <h2 class="admin-card-title">View jazz site</h2>
                <p class="admin-card-desc">Open the public jazz homepage.</p>
            </a>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
