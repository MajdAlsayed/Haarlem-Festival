<?php
$pageTitle = 'History CMS — Admin — Haarlem Festival';
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        <nav class="admin-breadcrumb">
            <a href="/">Haarlem Festival</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>History</span>
        </nav>

        <h1 class="admin-title">History CMS</h1>
        <p class="admin-lead">
            Manage History page content, locations, landmark detail pages, tours, and public History pages.
        </p>

        <section class="admin-cards">
            <a href="/admin/cms/history" class="admin-card">
                <span class="admin-card-icon">✏️</span>
                <h2 class="admin-card-title">History page</h2>
                <p class="admin-card-desc">
                    Edit the History homepage content, hero, intro text, cards, and sections.
                </p>
            </a>

            <a href="/admin/cms/history-locations" class="admin-card">
                <span class="admin-card-icon">📍</span>
                <h2 class="admin-card-title">Locations page</h2>
                <p class="admin-card-desc">
                    Edit the History locations overview page.
                </p>
            </a>

            <a href="/admin/cms/history/location/st-bavo" class="admin-card">
                <span class="admin-card-icon">⛪</span>
                <h2 class="admin-card-title">St. Bavo detail</h2>
                <p class="admin-card-desc">
                    Edit the Church of St. Bavo landmark detail page.
                </p>
            </a>

            <a href="/admin/cms/history/location/grote-markt" class="admin-card">
                <span class="admin-card-icon">🏛️</span>
                <h2 class="admin-card-title">Grote Markt detail</h2>
                <p class="admin-card-desc">
                    Edit the Grote Markt landmark detail page.
                </p>
            </a>

            <a href="/admin/cms/history-tours" class="admin-card">
                <span class="admin-card-icon">🎟️</span>
                <h2 class="admin-card-title">Tours page</h2>
                <p class="admin-card-desc">
                    Edit tour copy, schedule content, ticket text, and route information.
                </p>
            </a>

            <a href="/history" class="admin-card" target="_blank" rel="noopener">
                <span class="admin-card-icon">↗</span>
                <h2 class="admin-card-title">View History site</h2>
                <p class="admin-card-desc">
                    Open the public History homepage.
                </p>
            </a>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>