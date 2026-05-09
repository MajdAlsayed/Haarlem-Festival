<?php
/**
 * The friendly paragraph visitors read at the top of /tickets — stored as site_settings.tickets_intro. AdminTicketsController::settings().
 */
/** @var array $app */
/** @var string $intro */
/** @var string $csrf */
/** @var ?string $success */
/** @var ?string $error */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets intro — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/tickets">Tickets</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Intro</span>
        </nav>

        <h1 class="admin-title">Tickets page intro</h1>

        <?php if (!empty($success)): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/tickets/settings" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <div class="admin-field">
                <label for="tickets_intro">Lead paragraph (under breadcrumbs)</label>
                <textarea id="tickets_intro" name="tickets_intro" class="admin-input admin-textarea" rows="4" required><?= $h($intro) ?></textarea>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/tickets" class="admin-btn admin-btn-secondary">Back</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
