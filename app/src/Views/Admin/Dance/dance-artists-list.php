<?php

$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'Dance artists — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
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
            <span>Artists</span>
        </nav>

        <h1 class="admin-title">Dance artists (homepage strip)</h1>
        <p class="admin-hint" style="margin-bottom:1rem;">Artist cards shown on the public Dance page. Each artist links to its own profile page.</p>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <p>
            <a href="/admin/dance/artists/new" class="admin-btn admin-btn-primary">Add artist</a>
            <a href="/admin/dance" class="admin-btn admin-btn-secondary">Back to Dance CMS</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Bio</th>
                        <th>Image</th>
                        <th>Public page</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artists as $a): ?>
                        <?php if (!is_array($a)) { continue; } ?>
                    <tr>
                        <td><?= $h((string) ($a['name'] ?? '')) ?></td>
                        <td><code class="admin-slug"><?= $h((string) ($a['slug'] ?? '')) ?></code></td>
                        <td class="admin-muted"><?php
                            $bio = (string) ($a['bio'] ?? '');
                            echo $h(strlen($bio) > 80 ? substr($bio, 0, 77) . '...' : $bio);
                        ?></td>
                        <td class="admin-muted"><?= $h((string) ($a['image'] ?? '')) ?></td>
                        <td>
                            <?php $slug = (string) ($a['slug'] ?? ''); ?>
                            <?php if ($slug !== ''): ?>
                                <a href="/dance/artist/<?= $h(rawurlencode($slug)) ?>" target="_blank" rel="noopener">/dance/artist/<?= $h($slug) ?></a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($slug !== ''): ?>
                            <a href="/admin/dance/artists/edit?slug=<?= $h(rawurlencode($slug)) ?>" class="admin-btn admin-btn-sm admin-btn-primary">Edit</a>
                            <?php
                            $delForm = 'admin_dance_artist_del_' . preg_replace('/[^a-z0-9_]/i', '_', $slug);
                            $delTok = \App\Core\Csrf::token($delForm);
                            ?>
                            <form method="post" action="/admin/dance/artists/delete" style="display:inline;" onsubmit="return confirm('Remove this artist from the Dance page?');">
                                <input type="hidden" name="_csrf_form" value="<?= $h($delForm) ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($delTok) ?>">
                                <input type="hidden" name="slug" value="<?= $h($slug) ?>">
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Remove</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
