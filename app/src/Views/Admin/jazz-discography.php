<?php
/** @var array $app */
/** @var list<string> $slugs */
/** @var string $slug */
/** @var list<array<string,mixed>> $tracks */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discography — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/jazz">Jazz</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Discography</span>
        </nav>

        <h1 class="admin-title">Discography</h1>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="get" action="/admin/jazz/discography" class="admin-field" style="max-width: 24rem;">
            <label for="slug">Artist slug</label>
            <select name="slug" id="slug" class="admin-input" onchange="this.form.submit()">
                <?php foreach ($slugs as $s): ?>
                    <option value="<?= $h($s) ?>"<?= $s === $slug ? ' selected' : '' ?>><?= $h($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <p>
            <a href="/admin/jazz/discography/edit?slug=<?= $h(rawurlencode($slug)) ?>" class="admin-btn admin-btn-primary">Add track</a>
            <a href="/admin/jazz" class="admin-btn admin-btn-secondary">Back</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Image / audio</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tracks as $t): ?>
                    <tr>
                        <td><?= (int) ($t['sort_order'] ?? 0) ?></td>
                        <td><?= $h((string) ($t['title'] ?? '')) ?></td>
                        <td><code class="admin-slug"><?= $h((string) ($t['image_file'] ?? '')) ?></code><br><code class="admin-slug"><?= $h((string) ($t['audio_file'] ?? '')) ?></code></td>
                        <td>
                            <a href="/admin/jazz/discography/edit?id=<?= (int) $t['track_id'] ?>" class="admin-btn admin-btn-sm">Edit</a>
                            <?php
                            $df = 'admin_jazz_disc_del_' . (int) $t['track_id'];
                            $dt = \App\Core\Csrf::token($df);
                            ?>
                            <form method="post" action="/admin/jazz/discography/delete" style="display:inline;" onsubmit="return confirm('Delete this track?');">
                                <input type="hidden" name="_csrf_form" value="<?= $h($df) ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($dt) ?>">
                                <input type="hidden" name="track_id" value="<?= (int) $t['track_id'] ?>">
                                <input type="hidden" name="return_slug" value="<?= $h($slug) ?>">
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($tracks === []): ?>
            <p class="admin-muted">No tracks for this slug.</p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
