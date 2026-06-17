<?php

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$origSlug = $isNew ? '' : (string) ($row['slug'] ?? '');

$pageTitle = ($isNew ? 'New' : 'Edit') . ' dance artist — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/dance">Dance</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/dance/artists">Artists</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span><?= $isNew ? 'New' : 'Edit' ?></span>
        </nav>

        <h1 class="admin-title"><?= $isNew ? 'New dance artist' : 'Edit dance artist' ?></h1>

        <form method="post" action="/admin/dance/artists/save" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="original_slug" value="<?= $h($origSlug) ?>">

            <div class="admin-field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required class="admin-input" maxlength="120" value="<?= $h($row['name']) ?>">
            </div>

            <div class="admin-field">
                <label for="slug">URL slug</label>
                <input type="text" id="slug" name="slug" required class="admin-input" maxlength="80" pattern="[a-z0-9-]+" value="<?= $h($row['slug']) ?>">
                <small class="admin-hint">Lowercase letters, numbers and hyphens only. Used in the artist’s page address.</small>
            </div>

            <div class="admin-field">
                <label for="bio">Short bio</label>
                <textarea id="bio" name="bio" class="admin-input admin-textarea" rows="4" maxlength="2000"><?= $h($row['bio']) ?></textarea>
            </div>

            <div class="admin-field">
                <label for="image">Image filename</label>
                <input type="text" id="image" name="image" class="admin-input" maxlength="255" value="<?= $h($row['image']) ?>">
                <small class="admin-hint">Image filename only (e.g. hardwell-hero.png).</small>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/dance/artists" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
