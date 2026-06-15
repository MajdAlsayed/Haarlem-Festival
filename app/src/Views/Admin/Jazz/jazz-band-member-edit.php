<?php
/**
 * Add or change one person on an artist’s “meet the band” strip. AdminJazzController::editBandMember().
 */
/** @var array $app */
/** @var ?array<string,mixed> $member */
/** @var string $slug */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isNew = $member === null;
$m = $member ?? [];

$pageTitle = ($isNew ? 'Add' : 'Edit') . ' band member — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
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
            <a href="/admin/jazz">Jazz</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/jazz/band-members?slug=<?= $h(rawurlencode($slug)) ?>">Band members</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span><?= $isNew ? 'Add' : 'Edit' ?></span>
        </nav>

        <h1 class="admin-title"><?= $isNew ? 'Add band member' : 'Edit band member' ?></h1>

        <form method="post" action="/admin/jazz/band-members/save" enctype="multipart/form-data" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="member_id" value="<?= $isNew ? '0' : (int) ($m['member_id'] ?? 0) ?>">

            <div class="admin-field">
                <label for="artist_slug">Artist slug</label>
                <input type="text" id="artist_slug" name="artist_slug" value="<?= $h($isNew ? $slug : (string) ($m['artist_slug'] ?? '')) ?>" class="admin-input" required>
                <small class="admin-hint">e.g. <code>gumbo-kings</code> or <code>gare-du-nord</code> — must match the artist page URL slug.</small>
            </div>

            <div class="admin-field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= $h((string) ($m['name'] ?? '')) ?>" class="admin-input" required>
            </div>

            <div class="admin-field">
                <label for="role">Role / instrument</label>
                <input type="text" id="role" name="role" value="<?= $h((string) ($m['role'] ?? '')) ?>" class="admin-input" required>
            </div>

            <div class="admin-field">
                <label for="band_photo_upload">Upload photo</label>
                <input type="file" id="band_photo_upload" name="band_photo_upload" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
                <small class="admin-hint">Square-ish portrait works best. JPG/PNG/WebP/GIF, max ~10 MB. Overrides the path below when a file is chosen.</small>
            </div>
            <div class="admin-field">
                <label for="image_file">Or image filename / path under <code>images/jazz/</code></label>
                <input type="text" id="image_file" name="image_file" value="<?= $h((string) ($m['image_file'] ?? '')) ?>" class="admin-input">
                <small class="admin-hint"><?= $isNew
                    ? 'Required if you do not upload a photo above (e.g. Boy veilvoije.png or uploads/band-members/member-….jpg).'
                    : 'Leave blank to keep the current file when you are not uploading a new image.' ?></small>
            </div>

            <div class="admin-field">
                <label for="sort_order">Sort order</label>
                <input type="number" id="sort_order" name="sort_order" value="<?= $h((string) ($m['sort_order'] ?? 0)) ?>" class="admin-input">
                <small class="admin-hint">Lower numbers appear first (row 1: first three, row 2: the rest).</small>
            </div>

            <p>
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/jazz/band-members?slug=<?= $h(rawurlencode($slug)) ?>" class="admin-btn admin-btn-secondary">Cancel</a>
            </p>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
