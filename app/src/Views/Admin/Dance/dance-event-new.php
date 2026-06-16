<?php
/** @var array $app */
/** @var list<array{venue_id:int,name:string,city:string}> $venues */
/** @var string $csrf */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$days = ['thursday', 'friday', 'saturday', 'sunday'];

$pageTitle = 'New dance event — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
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
            <span>New</span>
        </nav>

        <h1 class="admin-title">New dance event</h1>
        <p class="admin-hint">After creating, use Edit to add preview audio if needed.</p>

        <form method="post" action="/admin/dance/events/save" class="admin-form admin-form--wide">
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
                    <small class="admin-hint">Total seats for this event — drives ticket availability.</small>
                </div>
                <div class="admin-field">
                    <label for="price">Price (optional)</label>
                    <input type="text" id="price" name="price" class="admin-input" placeholder="15.00">
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Create</button>
                <a href="/admin/dance/events" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
