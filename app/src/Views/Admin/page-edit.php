<?php
/** @var array $app */
/** @var array{page_id:int,slug:string,title:string,is_published:bool} $page */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit page — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/pages">Pages</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Edit</span>
        </nav>

        <h1 class="admin-title">Edit page</h1>

        <form method="post" action="/admin/pages/update" class="admin-form">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="page_id" value="<?= (int) $page['page_id'] ?>">

            <div class="admin-field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= $h($page['title']) ?>" required class="admin-input">
            </div>

            <div class="admin-field">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" value="<?= $h($page['slug']) ?>" class="admin-input" placeholder="e.g. about-us">
                <small class="admin-hint">URL-friendly: lowercase letters, numbers, hyphens only.</small>
            </div>

            <div class="admin-field admin-field-checkbox">
                <label>
                    <input type="checkbox" name="is_published" value="1"<?= $page['is_published'] ? ' checked' : '' ?>>
                    Published (visible on site)
                </label>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/pages" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
