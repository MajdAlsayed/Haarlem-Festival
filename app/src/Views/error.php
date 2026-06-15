<?php
$app = (new \App\Repositories\SettingsRepository())->getAll();

$pageTitle = 'Error ' . (int) $code;
$pageStyles = [];
$bodyClass = 'error-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
<div class="error-box">
    <h1>Error <?= (int) $code ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
    <p><a href="<?= htmlspecialchars($app['home_path']) ?>">Back to home</a></p>
</div>
</body>
</html>
