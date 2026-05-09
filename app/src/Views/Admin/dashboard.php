<?php
/**
 * Main admin dashboard (/admin): card grid into Pages, Jazz, Dance, Tickets, orders, scanner — each card is a separate CMS or tool area.
 * @var array $app
 * @var list<array{page_id:int,slug:string,title:string,is_published:bool}> $pages
 */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        <section class="admin-page-header">
            <div>
                <h1 class="admin-title">CMS Dashboard</h1>
                <p class="admin-lead">Manage your site content.</p>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-top admin-panel-top--dashboard">
                <h2 class="admin-section-heading">Content & Tools</h2>
                <p class="admin-section-note">Choose a section to manage content or admin functionality.</p>
            </div>

            <section class="admin-cards">
                <a href="/admin/pages" class="admin-card">
                    <span class="admin-card-icon">📄</span>
                    <h2 class="admin-card-title">Pages</h2>
                    <p class="admin-card-desc">Edit pages, slugs, and publish status.</p>
                    <span class="admin-card-count"><?= count($pages) ?> page(s)</span>
                </a>

                <a href="/admin/cms/homepage" class="admin-card">
                    <span class="admin-card-icon">🏠</span>
                    <h2 class="admin-card-title">Homepage</h2>
                    <p class="admin-card-desc">Edit homepage layout, sections and content blocks.</p>
                </a>

                <a href="/admin/jazz" class="admin-card">
                    <span class="admin-card-icon">🎷</span>
                    <h2 class="admin-card-title">Jazz</h2>
                    <p class="admin-card-desc">Events, layout, artist pages, discography.</p>
                </a>

                <a href="/admin/food" class="admin-card">
                    <span class="admin-card-icon">🍽️</span>
                    <h2 class="admin-card-title">Food</h2>
                    <p class="admin-card-desc">Restaurants, settings, reservation fee &amp; filters.</p>
                </a>

                <a href="/admin/dance" class="admin-card">
                    <span class="admin-card-icon">💃</span>
                    <h2 class="admin-card-title">Dance</h2>
                    <p class="admin-card-desc">Page copy, events, artist strip, images — Dance CMS hub.</p>
                </a>

                <a href="/admin/cms/history" class="admin-card">
                    <span class="admin-card-icon">🏛️</span>
                    <h2 class="admin-card-title">History</h2>
                    <p class="admin-card-desc">
                        Manage history page, locations, tours and related content.
                    </p>
                </a>

                <a href="/admin/stories" class="admin-card">
                    <span class="admin-card-icon">📚</span>
                    <h2 class="admin-card-title">Stories</h2>
                    <p class="admin-card-desc">Manage story cards and story detail pages.</p>
                </a>

                <a href="/admin/tickets" class="admin-card">
                    <span class="admin-card-icon">🎫</span>
                    <h2 class="admin-card-title">Tickets</h2>
                    <p class="admin-card-desc">Passes, event prices, tickets page intro.</p>
                </a>

                <a href="/admin/orders" class="admin-card">
                    <span class="admin-card-icon">📋</span>
                    <h2 class="admin-card-title">Order details</h2>
                    <p class="admin-card-desc">View all orders, customer data, and per-order ticket codes.</p>
                </a>

                <a href="/admin/scan" class="admin-card">
                    <span class="admin-card-icon">📱</span>
                    <h2 class="admin-card-title">Scan tickets</h2>
                    <p class="admin-card-desc">Check admission codes at the door (admin).</p>
                </a>

                <a href="/admin/users" class="admin-card">
                    <span class="admin-card-icon">👥</span>
                    <h2 class="admin-card-title">Users</h2>
                    <p class="admin-card-desc">Manage users, roles and access.</p>
                </a>
            </section>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
