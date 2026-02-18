<?php $app = (new \App\Repositories\SettingsRepository())->getAll(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error <?= (int) $code ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
    <style> .error-box { max-width: 600px; margin: 3rem auto; padding: 2rem; text-align: center; } </style>
</head>
<body>
<div class="error-box">
    <h1>Error <?= (int) $code ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
    <p><a href="<?= htmlspecialchars($app['home_path']) ?>">Back to home</a></p>
</div>
</body>
</html>
