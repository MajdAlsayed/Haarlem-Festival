<?php
/**
 * Jazz backstage landing page (/admin/jazz) — pick what you want to tweak: shows on the schedule, site copy, albums, or line-ups.
 * Wired up by AdminJazzController::index().
 */
/** @var array $app */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'Jazz CMS — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

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
        <p class="admin-lead">Manage jazz events, homepage layout, artist pages, band members, and discography.</p>

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
            <a href="/admin/jazz/band-members" class="admin-card">
                <span class="admin-card-icon">👥</span>
                <h2 class="admin-card-title">Band members</h2>
                <p class="admin-card-desc">Line-up, photos, and order per artist page.</p>
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

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
