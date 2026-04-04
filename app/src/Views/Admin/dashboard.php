<?php
/** @var array $app */
/** @var list<array{page_id:int,slug:string,title:string,is_published:bool}> $pages */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Admin</span>
        </nav>

        <h1 class="admin-title">CMS Dashboard</h1>
        <p class="admin-lead">Manage your site content.</p>

        <section class="admin-cards">
            <a href="/admin/pages" class="admin-card">
                <span class="admin-card-icon">📄</span>
                <h2 class="admin-card-title">Pages</h2>
                <p class="admin-card-desc">Edit pages, slugs, and publish status.</p>
                <span class="admin-card-count"><?= count($pages) ?> page(s)</span>
            </a>
            <a href="/admin/jazz" class="admin-card">
                <span class="admin-card-icon">🎷</span>
                <h2 class="admin-card-title">Jazz</h2>
                <p class="admin-card-desc">Events, layout, artist pages, discography.</p>
            </a>
            <a href="/admin/tickets" class="admin-card">
                <span class="admin-card-icon">🎫</span>
                <h2 class="admin-card-title">Tickets</h2>
                <p class="admin-card-desc">Passes, event prices, tickets page intro.</p>
            </a>
            <a href="/admin/scan" class="admin-card">
                <span class="admin-card-icon">📱</span>
                <h2 class="admin-card-title">Scan tickets</h2>
                <p class="admin-card-desc">Check admission codes at the door (admin).</p>
            </a>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
