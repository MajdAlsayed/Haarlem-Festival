<?php
/**
 * Dance CMS hub (/admin/dance): same card pattern as Jazz / Food — page CMS, events, artists, public link.
 * @var array $app
 */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dance CMS — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Dance</span>
        </nav>

        <h1 class="admin-title">Dance CMS</h1>
        <p class="admin-lead">Manage the Dance homepage, schedule, artist strip, and preview assets — same structure as Jazz and Food.</p>

        <section class="admin-cards">
            <a href="/admin/cms/dance" class="admin-card">
                <span class="admin-card-icon">✏️</span>
                <h2 class="admin-card-title">Dance page copy</h2>
                <p class="admin-card-desc">Hero, about, section titles, featured strip, day images (WYSIWYG).</p>
            </a>
            <a href="/admin/dance/events" class="admin-card">
                <span class="admin-card-icon">📅</span>
                <h2 class="admin-card-title">Events</h2>
                <p class="admin-card-desc">Dance events: venues, days, times, capacity, optional preview audio.</p>
            </a>
            <a href="/admin/dance/artists" class="admin-card">
                <span class="admin-card-icon">🎤</span>
                <h2 class="admin-card-title">Artists</h2>
                <p class="admin-card-desc">Homepage strip + public profile URLs: <code>/dance/artist/{slug}</code> (same slug as the <code>artists</code> table).</p>
            </a>
            <a href="/dance" class="admin-card" target="_blank" rel="noopener">
                <span class="admin-card-icon">↗</span>
                <h2 class="admin-card-title">View Dance site</h2>
                <p class="admin-card-desc">Open the public Dance homepage.</p>
            </a>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
