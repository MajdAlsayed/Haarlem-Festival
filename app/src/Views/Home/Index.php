<?php
/** Public homepage template: $viewModel from HomeController; $cmsHome is merged settings (edited in /admin/cms/homepage). */
$page = $viewModel->page;
$categories = $viewModel->categories;
$cmsHome = $viewModel->cmsHome;
// shared for header/footer and any partial that needs them
$app = (new \App\Repositories\SettingsRepository())->getAll();
$navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) ($page->title ?? '')) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string) ($app['css_version'] ?? '1')) ?>">
</head>
<body class="home-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <?php require __DIR__ . '/../partials/hero.php'; ?>
    <?php require __DIR__ . '/../partials/welcome.php'; ?>
    <?php require __DIR__ . '/../partials/events.php'; ?>
    <?php require __DIR__ . '/../partials/about.php'; ?>
    <?php require __DIR__ . '/../partials/expect.php'; ?>
    <?php require __DIR__ . '/../partials/map.php'; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
