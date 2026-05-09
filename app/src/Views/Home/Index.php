<?php
/** Public homepage template: $viewModel from HomeController; $cmsHome is merged settings (edited in /admin/cms/homepage). */
$page = $viewModel->page;
$categories = $viewModel->categories;
$cmsHome = $viewModel->cmsHome;
// shared for header/footer and any partial that needs them
$app = (new \App\Repositories\SettingsRepository())->getAll();
$navLinks = (new \App\Repositories\MenuRepository())->getNavLinks();

// Settings for the page title, styles, body class
$pageTitle = (string) ($page->title ?? ($app['site_name'] ?? 'Haarlem Festival'));
$pageStyles = ['/css/pages/home.css'];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body>

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
