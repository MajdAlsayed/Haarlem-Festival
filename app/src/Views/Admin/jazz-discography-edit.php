<?php
/**
 * Single track form (title, year, audio file, artwork, …) for the selected artist slug. AdminJazzController::editDiscTrack().
 */
/** @var array $app */
/** @var ?array<string,mixed> $track */
/** @var string $slug */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isNew = $track === null;
$t = $track ?? [];
$releaseYearVal = $t['release_year'] ?? null;
$durationSecVal = $t['duration_seconds'] ?? null;
$releaseYearAttr = ($releaseYearVal !== null && $releaseYearVal !== '') ? $h((string) $releaseYearVal) : '';
$durationSecAttr = ($durationSecVal !== null && $durationSecVal !== '') ? $h((string) $durationSecVal) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isNew ? 'Add' : 'Edit' ?> track — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/jazz/discography?slug=<?= $h(rawurlencode($slug)) ?>">Discography</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span><?= $isNew ? 'Add' : 'Edit' ?></span>
        </nav>

        <h1 class="admin-title"><?= $isNew ? 'Add discography track' : 'Edit track' ?></h1>

        <form method="post" action="/admin/jazz/discography/save" enctype="multipart/form-data" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="track_id" value="<?= $isNew ? '0' : (int) ($t['track_id'] ?? 0) ?>">

            <div class="admin-field">
                <label for="artist_slug">Artist slug</label>
                <input type="text" id="artist_slug" name="artist_slug" value="<?= $h($isNew ? $slug : (string) ($t['artist_slug'] ?? '')) ?>" class="admin-input" required>
                <small class="admin-hint">e.g. <code>karsu</code> — must match URL slug / discography query.</small>
            </div>

            <div class="admin-field">
                <label for="title">Track title</label>
                <input type="text" id="title" name="title" value="<?= $h((string) ($t['title'] ?? '')) ?>" class="admin-input" required>
            </div>

            <div class="admin-field">
                <label for="disc_image_upload">Upload cover image</label>
                <input type="file" id="disc_image_upload" name="disc_image_upload" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,.jpg,.jpeg,.png,.webp,.gif">
                <small class="admin-hint">JPG/PNG/WebP/GIF, max ~10 MB. Overrides the path field below when a file is chosen.</small>
            </div>
            <div class="admin-field">
                <label for="image_file">Or cover filename / path under <code>images/jazz/</code></label>
                <input type="text" id="image_file" name="image_file" value="<?= $h((string) ($t['image_file'] ?? '')) ?>" class="admin-input">
                <small class="admin-hint"><?= $isNew
                    ? 'Required if you do not upload a cover image above.'
                    : 'Leave blank to keep the current file when you are not uploading a new image.' ?></small>
            </div>

            <div class="admin-field">
                <label for="disc_audio_upload">Upload track audio</label>
                <input type="file" id="disc_audio_upload" name="disc_audio_upload" class="admin-input" accept="audio/mpeg,audio/wav,audio/flac,audio/ogg,.mp3,.wav,.flac,.ogg,.m4a">
                <small class="admin-hint">MP3/WAV/FLAC/OGG/M4A, max ~50 MB. Overrides the path field below when a file is chosen.</small>
            </div>
            <div class="admin-field">
                <label for="audio_file">Or audio path under <code>public/audio/</code></label>
                <input type="text" id="audio_file" name="audio_file" value="<?= $h((string) ($t['audio_file'] ?? '')) ?>" class="admin-input">
                <small class="admin-hint"><?= $isNew
                    ? 'Required if you do not upload a track file above.'
                    : 'Leave blank to keep the current file when you are not uploading new audio.' ?></small>
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="release_year">Release year</label>
                    <input type="number" id="release_year" name="release_year" value="<?= $releaseYearAttr ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="duration_seconds">Duration (seconds)</label>
                    <input type="number" id="duration_seconds" name="duration_seconds" value="<?= $durationSecAttr ?>" class="admin-input">
                </div>
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="play_count">Play count</label>
                    <input type="number" id="play_count" name="play_count" value="<?= $h((string) ($t['play_count'] ?? 0)) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="sort_order">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= $h((string) ($t['sort_order'] ?? 0)) ?>" class="admin-input">
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/jazz/discography?slug=<?= $h(rawurlencode($slug)) ?>" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
