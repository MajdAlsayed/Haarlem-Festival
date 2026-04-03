<?php
/** @var array $app */
/** @var ?array<string,mixed> $row */
/** @var string $csrf */
/** @var list<array<string,mixed>> $allEvents */
/** @var list<array<string,mixed>> $eventsMissing */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$isNew = $row === null;
$r = $row ?? [];
$id = $isNew ? 0 : (int) ($r['ticket_details_id'] ?? 0);
$type = (string) ($r['ticket_type'] ?? 'day_pass');
$cat = (string) ($r['category'] ?? 'jazz');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $isNew ? 'New' : 'Edit' ?> ticket — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/tickets">Tickets</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span><?= $isNew ? 'New' : 'Edit' ?></span>
        </nav>

        <h1 class="admin-title"><?= $isNew ? 'New ticket or pass' : 'Edit ticket' ?></h1>
        <p class="admin-hint" style="margin-bottom:1rem;">Day pass and all-access pass rows are shown on the live Tickets page only for <strong>Jazz</strong> and <strong>Dance</strong>. For History and Stories events, create an <strong>Event ticket</strong> only.</p>

        <?php if ($eventsMissing !== [] && $isNew): ?>
            <p class="admin-hint">Events without a ticket row yet: <?= count($eventsMissing) ?>. Pick <strong>Event ticket</strong> and select one.</p>
        <?php endif; ?>

        <form method="post" action="/admin/tickets/save" class="admin-form admin-form--wide" id="ticketForm">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <input type="hidden" name="ticket_details_id" value="<?= $id ?>">

            <div class="admin-field">
                <label for="ticket_type">Type</label>
                <select name="ticket_type" id="ticket_type" class="admin-input">
                    <option value="event_ticket"<?= $type === 'event_ticket' ? ' selected' : '' ?>>Event ticket</option>
                    <option value="day_pass"<?= $type === 'day_pass' ? ' selected' : '' ?>>Day pass</option>
                    <option value="all_access_pass"<?= $type === 'all_access_pass' ? ' selected' : '' ?>>All-access pass</option>
                </select>
            </div>

            <div class="admin-field" id="field_event">
                <label for="event_id">Event (for event tickets)</label>
                <select name="event_id" id="event_id" class="admin-input">
                    <option value="0">—</option>
                    <?php foreach ($allEvents as $ev): ?>
                        <option value="<?= (int) $ev['event_id'] ?>"<?= !$isNew && (int) ($r['event_id'] ?? 0) === (int) $ev['event_id'] ? ' selected' : '' ?>>
                            <?= $h('[' . ($ev['cat'] ?? '') . '] ' . ($ev['title'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-field">
                <label for="category">Tab / category (passes)</label>
                <select name="category" id="category" class="admin-input">
                    <?php foreach (['all', 'jazz', 'dance', 'history', 'stories'] as $c): ?>
                        <option value="<?= $h($c) ?>"<?= $cat === $c ? ' selected' : '' ?>><?= $h($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="admin-hint">For passes: use <strong>jazz</strong> or <strong>dance</strong> (or <strong>all</strong>). History/stories are not allowed for day or all-access passes.</small>
            </div>

            <div class="admin-field">
                <label for="name">Title on card</label>
                <input type="text" id="name" name="name" class="admin-input" required value="<?= $h((string) ($r['name'] ?? '')) ?>">
            </div>

            <div class="admin-field">
                <label for="description">Subtitle / venue line</label>
                <textarea id="description" name="description" class="admin-input admin-textarea" rows="2"><?= $h((string) ($r['description'] ?? '')) ?></textarea>
            </div>

            <div class="admin-grid-2" id="field_pass">
                <div class="admin-field">
                    <label for="pass_day">Pass day (e.g. thursday)</label>
                    <input type="text" id="pass_day" name="pass_day" class="admin-input" value="<?= $h((string) ($r['pass_day'] ?? '')) ?>">
                </div>
                <div class="admin-field">
                    <label for="pass_time">Pass time (optional)</label>
                    <input type="text" id="pass_time" name="pass_time" class="admin-input" value="<?= $h((string) ($r['pass_time'] ?? '')) ?>">
                </div>
            </div>

            <div class="admin-field" id="field_schedule">
                <label for="schedule_display">Schedule line (all-access)</label>
                <input type="text" id="schedule_display" name="schedule_display" class="admin-input" value="<?= $h((string) ($r['schedule_display'] ?? '')) ?>" placeholder="Thursday, Friday, Saturday">
            </div>

            <div class="admin-grid-2">
                <div class="admin-field">
                    <label for="price">Price (€)</label>
                    <input type="text" id="price" name="price" class="admin-input" value="<?= $h((string) ($r['price'] ?? '0')) ?>">
                </div>
                <div class="admin-field">
                    <label for="sort_order">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" class="admin-input" value="<?= $h((string) ($r['sort_order'] ?? '0')) ?>">
                </div>
            </div>

            <div class="admin-field admin-field-checkbox">
                <label>
                    <input type="checkbox" name="is_free" value="1"<?= !empty($r['is_free']) ? ' checked' : '' ?>>
                    Free (show FREE, no paid checkout)
                </label>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save</button>
                <a href="/admin/tickets" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
(function () {
    var sel = document.getElementById('ticket_type');
    var fe = document.getElementById('field_event');
    var fp = document.getElementById('field_pass');
    var fs = document.getElementById('field_schedule');
    function sync() {
        var t = sel.value;
        fe.style.display = t === 'event_ticket' ? '' : 'none';
        fp.style.display = t === 'day_pass' ? '' : 'none';
        fs.style.display = t === 'all_access_pass' ? '' : 'none';
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
