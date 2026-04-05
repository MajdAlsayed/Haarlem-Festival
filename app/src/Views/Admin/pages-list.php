<?php
/** @var array $app */
/** @var list<array{page_id:int,slug:string,title:string,is_published:bool}> $pages */
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pages — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Pages</span>
        </nav>

        <h1 class="admin-title">Pages</h1>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $p): ?>
                    <tr>
                        <td><?= $h($p['title']) ?></td>
                        <td><code class="admin-slug"><?= $h($p['slug']) ?></code></td>
                        <td>
                            <?php if ($p['is_published']): ?>
                                <span class="admin-badge admin-badge-published">Published</span>
                            <?php else: ?>
                                <span class="admin-badge admin-badge-draft">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/admin/pages/edit?id=<?= (int) $p['page_id'] ?>" class="admin-btn admin-btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($pages)): ?>
            <p class="admin-muted">No pages yet.</p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
