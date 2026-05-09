<?php
/**
 * Master list of everything you can sell — passes, single shows, freebies — with how many seats exist and how full they are.
 * You can tick several rows and bulk-delete (with safeguards for old orders). AdminTicketsController::index().
 */

use App\Repositories\TicketDetailsRepository;

/** @var array $app */
/** @var list<array<string,mixed>> $rows */
/** @var array<int, ?int> $capacities */
/** @var array<int, array{sold_out: bool, nearly: bool, low_stock: bool, remaining: ?int}> $stock */
/** @var string $bulkDeleteCsrf */
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
        <p class="admin-hint admin-hint--block">
            <strong>Deleting tickets:</strong> Use <strong>Delete selected</strong> (checkbox column + toolbar button) to remove many rows at once, or delete one-by-one in the last column. If a ticket was already sold or is in a cart, existing <strong>order lines</strong> stay valid via an internal <strong>archive placeholder</strong>; open <strong>carts</strong> lose that line. The placeholder row (category <code>internal</code>) cannot be edited or deleted.
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
            <form id="admin-tickets-bulk-delete" method="post" action="/admin/tickets/delete-bulk" class="admin-tickets-bulk-form" onsubmit="var n=document.querySelectorAll('.admin-ticket-bulk-cb:checked').length; if(n===0){alert('Select at least one ticket.');return false;} return confirm('Delete '+n+' ticket(s) from the catalog? Same rules as single delete (orders keep placeholder; carts lose the line).');">
                <input type="hidden" name="_csrf" value="<?= $h($bulkDeleteCsrf) ?>">
                <button type="submit" class="admin-btn admin-btn-danger">Delete selected</button>
            </form>
        </div>

        <div class="admin-tickets-table-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="admin-tickets-select-col" scope="col"><label class="admin-sr-only" for="admin-tickets-select-all">Select all on page</label><input type="checkbox" id="admin-tickets-select-all" title="Select all"></th>
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
                    $isArchivePlaceholder = ($r['name'] ?? '') === TicketDetailsRepository::ARCHIVE_PLACEHOLDER_NAME
                        && strcasecmp(trim((string) ($r['category'] ?? '')), TicketDetailsRepository::ARCHIVE_PLACEHOLDER_CATEGORY) === 0;
                    ?>
                    <tr>
                        <td class="admin-tickets-select-col"><?php if ($isArchivePlaceholder): ?>
                            <span class="admin-muted" title="Cannot delete">—</span>
                        <?php else: ?>
                            <input type="checkbox" class="admin-ticket-bulk-cb" form="admin-tickets-bulk-delete" name="ticket_details_id[]" value="<?= (int) $r['ticket_details_id'] ?>">
                        <?php endif; ?></td>
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
                            <?php if ($isArchivePlaceholder): ?>
                                <span class="admin-muted" title="Keeps historical order lines valid">System</span>
                            <?php else: ?>
                                <a href="/admin/tickets/edit?id=<?= (int) $r['ticket_details_id'] ?>" class="admin-btn admin-btn-sm">Edit</a>
                                <?php
                                $df = 'admin_td_del_' . (int) $r['ticket_details_id'];
                                $dt = \App\Core\Csrf::token($df);
                                ?>
                                <form method="post" action="/admin/tickets/delete" style="display:inline;" onsubmit="return confirm('Delete this ticket from the catalog? If it was sold, existing orders will show an internal archive placeholder for that line; carts will drop this item.');">
                                    <input type="hidden" name="_csrf_form" value="<?= $h($df) ?>">
                                    <input type="hidden" name="_csrf" value="<?= $h($dt) ?>">
                                    <input type="hidden" name="ticket_details_id" value="<?= (int) $r['ticket_details_id'] ?>">
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
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

<script>
(function () {
    var master = document.getElementById('admin-tickets-select-all');
    if (!master) return;
    master.addEventListener('change', function () {
        document.querySelectorAll('.admin-ticket-bulk-cb').forEach(function (cb) {
            cb.checked = master.checked;
        });
    });
})();
</script>
</body>
</html>
