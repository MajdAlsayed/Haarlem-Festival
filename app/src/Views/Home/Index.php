<?php
$page = $viewModel->page;
$events = $viewModel->events;
$app = (new \App\Repositories\SettingsRepository())->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title><?= htmlspecialchars($page->title ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=20">

    <title><?= htmlspecialchars($page->title) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">

</head>
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
