<?php
/** @var array $app */
/** @var list<array<string,mixed>> $rows */
/** @var array<int, ?int> $capacities */
/** @var array<int, array{sold_out: bool, nearly: bool, low_stock: bool, remaining: ?int}> $stock */
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets catalog — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/admin.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="admin-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main admin-tickets-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Tickets</span>
        </nav>

        <h1 class="admin-title">Tickets catalog</h1>
        <p class="admin-lead">On the website, the <strong>Special offer</strong> block (day pass / all-access) appears only under <strong>Jazz</strong> and <strong>Dance</strong>. History and Stories only list per-event tickets. You cannot save a pass with category History or Stories.</p>
        <p class="admin-hint admin-hint--block">
            <strong>Capacity &amp; stock:</strong> Restaurant seating is edited under <a href="/admin/food">Food → Restaurants</a>.
            For <strong>music events</strong>, capacity is on the event (Jazz: <a href="/admin/jazz/events">Jazz → Events</a>; Dance: <a href="/admin/dance/events">Dance → Events</a> → Edit → <em>Capacity (seats)</em>).
            This table shows live <strong>capacity</strong> and <strong>remaining</strong> (sold + carts + unpaid holds).
        </p>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <div class="admin-tickets-toolbar">
            <a href="/admin/tickets/new" class="admin-btn admin-btn-primary">New ticket / pass</a>
            <a href="/admin/tickets/settings" class="admin-btn admin-btn-secondary">Tickets page intro</a>
            <a href="/tickets" class="admin-btn admin-btn-secondary" target="_blank" rel="noopener">View site</a>
            <a href="/admin" class="admin-btn admin-btn-secondary">Dashboard</a>
        </div>

        <div class="admin-tickets-table-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Event</th>
                        <th>Capacity</th>
                        <th>Remaining</th>
                        <th>Change capacity</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <?php
                    $tid = (int) ($r['ticket_details_id'] ?? 0);
                    $cap = $capacities[$tid] ?? null;
                    $pst = $stock[$tid] ?? null;
                    $rem = is_array($pst) ? $pst['remaining'] : null;
                    $tt = (string) ($r['ticket_type'] ?? '');
                    $eid = (int) ($r['event_id'] ?? 0);
                    $sessId = $r['session_id'] ?? null;
                    $evType = isset($r['event_type_name']) && is_string($r['event_type_name']) ? strtolower(trim($r['event_type_name'])) : '';
                    ?>
                    <tr>
                        <td><?= (int) $r['ticket_details_id'] ?></td>
                        <td><code class="admin-slug"><?= $h((string) ($r['ticket_type'] ?? '')) ?></code></td>
                        <td><?= $h((string) ($r['category'] ?? '')) ?></td>
                        <td><?= $h((string) ($r['name'] ?? '')) ?></td>
                        <td class="admin-muted"><?= $h((string) ($r['event_title'] ?? '—')) ?></td>
                        <td class="admin-muted"><?php
                            if (in_array($tt, ['day_pass', 'all_access_pass'], true)) {
                                echo '—';
                            } elseif ($cap !== null) {
                                echo $h((string) (int) $cap);
                            } else {
                                echo '<span title="Set events.seats or sessions.tickets_available">—</span>';
                            }
                        ?></td>
                        <td class="admin-muted"><?php
                            if (in_array($tt, ['day_pass', 'all_access_pass'], true)) {
                                echo '—';
                            } elseif ($rem !== null) {
                                echo $h((string) (int) $rem);
                                if (!empty($pst['sold_out'])) {
                                    echo ' <strong>Sold out</strong>';
                                }
                            } else {
                                echo '—';
                            }
                        ?></td>
                        <td class="admin-muted admin-tickets-cap-hint"><?php
                            if (in_array($tt, ['day_pass', 'all_access_pass'], true)) {
                                echo 'Passes: no fixed capacity';
                            } elseif ($sessId !== null && $sessId !== '') {
                                echo 'Session row: set <code>sessions.tickets_available</code>';
                            } elseif ($eid > 0 && $evType === 'jazz') {
                                echo '<a href="/admin/jazz/events/edit?id=' . (int) $eid . '">Jazz event → capacity</a>';
                            } elseif ($eid > 0 && $evType === 'dance') {
                                echo '<a href="/admin/dance/events/edit?id=' . (int) $eid . '">Dance event → capacity</a>';
                            } elseif ($eid > 0) {
                                echo $h(ucfirst($evType !== '' ? $evType : 'event')) . ' #' . $eid . ' — set <code>events.seats</code>';
                            } else {
                                echo '—';
                            }
                        ?></td>
                        <td><?= !empty($r['is_free']) ? 'FREE' : '€' . $h((string) ($r['price'] ?? '')) ?></td>
                        <td>
                            <a href="/admin/tickets/edit?id=<?= (int) $r['ticket_details_id'] ?>" class="admin-btn admin-btn-sm">Edit</a>
                            <?php
                            $df = 'admin_td_del_' . (int) $r['ticket_details_id'];
                            $dt = \App\Core\Csrf::token($df);
                            ?>
                            <form method="post" action="/admin/tickets/delete" style="display:inline;" onsubmit="return confirm('Delete this row?');">
                                <input type="hidden" name="_csrf_form" value="<?= $h($df) ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($dt) ?>">
                                <input type="hidden" name="ticket_details_id" value="<?= (int) $r['ticket_details_id'] ?>">
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
