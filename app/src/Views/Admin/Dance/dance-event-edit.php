<?php
/** @var array $app */
/** @var array<string,mixed> $event */
/** @var list<array{venue_id:int,name:string,city:string}> $venues */
/** @var ?array{audio_id:int,event_id:int,file_path:string,track_title:?string} $audio */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$days = ['thursday', 'friday', 'saturday', 'sunday'];
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');

$pageTitle = 'Edit dance event — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
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
            <a href="/admin/dance/events">Events</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Edit</span>
        </nav>

        <h1 class="admin-title">Edit dance event</h1>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/dance/events/save" class="admin-form admin-form--wide">
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
                    <label for="seats">Capacity (seats)</label>
                    <input type="number" id="seats" name="seats" value="<?= $event['seats'] !== null ? $h((string) $event['seats']) : '' ?>" class="admin-input" min="0" placeholder="e.g. 120">
                    <small class="admin-hint">Total seats for this event — drives ticket availability, so it shows “Only X left” / “Sold out” and never oversells.</small>
                </div>
                <div class="admin-field">
                    <label for="price">Price (optional)</label>
                    <input type="text" id="price" name="price" value="<?= $h($event['price'] ?? '') ?>" class="admin-input" placeholder="15.00">
                </div>
            </div>

            <fieldset class="admin-fieldset">
                <legend>Preview audio (optional)</legend>
                <p class="admin-hint">Optional short preview clip for this event.</p>
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
                <a href="/admin/dance/events" class="admin-btn admin-btn-secondary">Back</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
