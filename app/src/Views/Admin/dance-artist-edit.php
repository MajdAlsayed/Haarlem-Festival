<?php
/** @var array $app */
/** @var string $csrf */
/** @var bool $isNew */
/** @var array{name:string,slug:string,bio:string,image:string} $row */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$origSlug = $isNew ? '' : (string) ($row['slug'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isNew ? 'New' : 'Edit' ?> dance artist — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="admin-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

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
                <small class="admin-hint">Lowercase letters, numbers, hyphens only — must match <code>/dance/artist/{slug}</code> and the <code>artists</code> table for detail pages.</small>
            </div>

            <div class="admin-field">
                <label for="bio">Short bio</label>
                <textarea id="bio" name="bio" class="admin-input admin-textarea" rows="4" maxlength="2000"><?= $h($row['bio']) ?></textarea>
            </div>

            <div class="admin-field">
                <label for="image">Image filename</label>
                <input type="text" id="image" name="image" class="admin-input" maxlength="255" value="<?= $h($row['image']) ?>">
                <small class="admin-hint">Filename only, under <code>/public/images/dance/</code> (e.g. <code>Artist/hardwell hero.png</code>).</small>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/dance/artists" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
