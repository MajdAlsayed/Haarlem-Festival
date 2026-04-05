<?php
/** @var array $app */
/** @var array<string,mixed> $event */
/** @var list<array{venue_id:int,name:string,city:string}> $venues */
/** @var ?array{audio_id:int,event_id:int,file_path:string,track_title:?string} $audio */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$days = ['thursday', 'friday', 'saturday', 'sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit jazz event — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Edit</span>
        </nav>

        <h1 class="admin-title">Edit jazz event</h1>

        <form method="post" action="/admin/jazz/events/save" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="event_id" value="<?= (int) $event['event_id'] ?>">

            <div class="admin-field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= $h($event['title']) ?>" required class="admin-input">
            </div>

            <div class="admin-field">
                <label for="description">Description / bio snippet</label>
                <textarea id="description" name="description" class="admin-input admin-textarea" rows="6"><?= $h($event['description']) ?></textarea>
            </div>

            <div class="admin-field">
                <label for="venue_id">Venue</label>
                <select id="venue_id" name="venue_id" class="admin-input" required>
                    <?php foreach ($venues as $v): ?>
                        <option value="<?= (int) $v['venue_id'] ?>"<?= (int) $v['venue_id'] === (int) $event['venue_id'] ? ' selected' : '' ?>>
                            <?= $h($v['name'] . ' — ' . $v['city']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-field">
                <label for="event_day">Day</label>
                <select id="event_day" name="event_day" class="admin-input">
                    <?php foreach ($days as $d): ?>
                        <option value="<?= $h($d) ?>"<?= ($event['event_day'] ?? '') === $d ? ' selected' : '' ?>><?= $h(ucfirst($d)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="start_time">Start (HH:MM)</label>
                    <input type="text" id="start_time" name="start_time" value="<?= $h($event['start_time']) ?>" required class="admin-input" placeholder="18:00">
                </div>
                <div class="admin-field">
                    <label for="end_time">End (optional)</label>
                    <input type="text" id="end_time" name="end_time" value="<?= $h($event['end_time']) ?>" class="admin-input" placeholder="19:00">
                </div>
            </div>

            <div class="admin-field">
                <label for="hall">Hall (optional)</label>
                <input type="text" id="hall" name="hall" value="<?= $h($event['hall']) ?>" class="admin-input">
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="seats">Seats (optional)</label>
                    <input type="number" id="seats" name="seats" value="<?= $event['seats'] !== null ? $h((string) $event['seats']) : '' ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="price">Price (optional)</label>
                    <input type="text" id="price" name="price" value="<?= $h($event['price'] ?? '') ?>" class="admin-input" placeholder="15.00">
                </div>
            </div>

            <fieldset class="admin-fieldset">
                <legend>Preview audio (optional)</legend>
                <p class="admin-hint">Path under <code>public/audio/</code>, use forward slashes (e.g. <code>Jazz audio/track.mp3</code>).</p>
                <div class="admin-field">
                    <label for="preview_audio_path">File path</label>
                    <input type="text" id="preview_audio_path" name="preview_audio_path" value="<?= $h($audio['file_path'] ?? '') ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="preview_audio_title">Track title (optional)</label>
                    <input type="text" id="preview_audio_title" name="preview_audio_title" value="<?= $h($audio['track_title'] ?? '') ?>" class="admin-input">
                </div>
                <?php if ($audio): ?>
                <div class="admin-field admin-field-checkbox">
                    <label>
                        <input type="checkbox" name="clear_preview_audio" value="1">
                        Remove preview audio
                    </label>
                </div>
                <?php endif; ?>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/jazz/events" class="admin-btn admin-btn-secondary">Back</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
