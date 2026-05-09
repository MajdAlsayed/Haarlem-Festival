<?php
/**
 * Form for adding another jazz show (venue, day, times, price, …). AdminJazzController::newEvent().
 */
/** @var array $app */
/** @var list<array{venue_id:int,name:string,city:string}> $venues */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$days = ['thursday', 'friday', 'saturday', 'sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New jazz event — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/jazz/events">Events</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>New</span>
        </nav>

        <h1 class="admin-title">New jazz event</h1>
        <p class="admin-hint">You can attach preview audio now (upload or path) or add it later on Edit.</p>

        <form method="post" action="/admin/jazz/events/save" enctype="multipart/form-data" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="event_id" value="0">

            <div class="admin-field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required class="admin-input">
            </div>

            <div class="admin-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="admin-input admin-textarea" rows="5"></textarea>
            </div>

            <div class="admin-field">
                <label for="venue_id">Venue</label>
                <select id="venue_id" name="venue_id" class="admin-input" required>
                    <?php foreach ($venues as $v): ?>
                        <option value="<?= (int) $v['venue_id'] ?>"><?= $h($v['name'] . ' — ' . $v['city']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-field">
                <label for="event_day">Day</label>
                <select id="event_day" name="event_day" class="admin-input">
                    <?php foreach ($days as $d): ?>
                        <option value="<?= $h($d) ?>"><?= $h(ucfirst($d)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="start_time">Start (HH:MM)</label>
                    <input type="text" id="start_time" name="start_time" required class="admin-input" placeholder="18:00">
                </div>
                <div class="admin-field">
                    <label for="end_time">End (optional)</label>
                    <input type="text" id="end_time" name="end_time" class="admin-input">
                </div>
            </div>

            <div class="admin-field">
                <label for="hall">Hall (optional)</label>
                <input type="text" id="hall" name="hall" class="admin-input">
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="seats">Capacity (seats)</label>
                    <input type="number" id="seats" name="seats" class="admin-input" min="0" placeholder="e.g. 120">
                    <small class="admin-hint">Stored on <code>events.seats</code>; drives ticket availability for this concert.</small>
                </div>
                <div class="admin-field">
                    <label for="price">Price (optional)</label>
                    <input type="text" id="price" name="price" class="admin-input" placeholder="15.00">
                </div>
            </div>

            <fieldset class="admin-fieldset">
                <legend>Preview audio (optional)</legend>
                <p class="admin-hint">MP3/WAV/FLAC/OGG/M4A upload (max ~50 MB), or path under <code>public/audio/</code>. Upload wins if both are set.</p>
                <div class="admin-field">
                    <label for="preview_audio_upload">Upload audio</label>
                    <input type="file" id="preview_audio_upload" name="preview_audio_upload" class="admin-input" accept="audio/mpeg,audio/wav,audio/flac,audio/ogg,.mp3,.wav,.flac,.ogg,.m4a">
                </div>
                <div class="admin-field">
                    <label for="preview_audio_path">Or file path</label>
                    <input type="text" id="preview_audio_path" name="preview_audio_path" class="admin-input" placeholder="Jazz audio/track.mp3">
                </div>
                <div class="admin-field">
                    <label for="preview_audio_title">Track title (optional)</label>
                    <input type="text" id="preview_audio_title" name="preview_audio_title" class="admin-input">
                </div>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Create</button>
                <a href="/admin/jazz/events" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
