<?php
/**
 * List who’s in the band for each jazz artist page (photo + role + order). AdminJazzController::bandMembers().
 */
/** @var array $app */
/** @var list<string> $slugs */
/** @var string $slug */
/** @var list<array<string,mixed>> $members */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Band members — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Band members</span>
        </nav>

        <h1 class="admin-title">Band members</h1>
        <p class="admin-lead">Photos and roles for artist pages (e.g. Gumbo Kings, Gare du Nord). Order matches sort order, then row layout (first three, then the rest).</p>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="get" action="/admin/jazz/band-members" class="admin-field" style="max-width: 24rem;">
            <label for="slug">Artist slug</label>
            <select name="slug" id="slug" class="admin-input" onchange="this.form.submit()">
                <?php foreach ($slugs as $s): ?>
                    <option value="<?= $h($s) ?>"<?= $s === $slug ? ' selected' : '' ?>><?= $h($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <p>
            <a href="/admin/jazz/band-members/edit?slug=<?= $h(rawurlencode($slug)) ?>" class="admin-btn admin-btn-primary">Add member</a>
            <a href="/admin/jazz" class="admin-btn admin-btn-secondary">Back</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Image</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $m): ?>
                    <tr>
                        <td><?= (int) ($m['sort_order'] ?? 0) ?></td>
                        <td><?= $h((string) ($m['name'] ?? '')) ?></td>
                        <td><?= $h((string) ($m['role'] ?? '')) ?></td>
                        <td><code class="admin-slug"><?= $h((string) ($m['image_file'] ?? '')) ?></code></td>
                        <td>
                            <a href="/admin/jazz/band-members/edit?id=<?= (int) $m['member_id'] ?>" class="admin-btn admin-btn-sm">Edit</a>
                            <?php
                            $df = 'admin_jazz_band_del_' . (int) $m['member_id'];
                            $dt = \App\Core\Csrf::token($df);
                            ?>
                            <form method="post" action="/admin/jazz/band-members/delete" style="display:inline;" onsubmit="return confirm('Remove this band member?');">
                                <input type="hidden" name="_csrf_form" value="<?= $h($df) ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($dt) ?>">
                                <input type="hidden" name="member_id" value="<?= (int) $m['member_id'] ?>">
                                <input type="hidden" name="return_slug" value="<?= $h($slug) ?>">
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($members === []): ?>
            <p class="admin-muted">No members for this slug. Add some, or run migrations to load defaults.</p>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
