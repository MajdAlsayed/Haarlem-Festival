<?php
/** @var array $app */
/** @var list<array<string,mixed>> $rows */
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

<main class="admin-main">
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

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <p>
            <a href="/admin/tickets/new" class="admin-btn admin-btn-primary">New ticket / pass</a>
            <a href="/admin/tickets/settings" class="admin-btn admin-btn-secondary">Tickets page intro</a>
            <a href="/tickets" class="admin-btn admin-btn-secondary" target="_blank" rel="noopener">View site</a>
            <a href="/admin" class="admin-btn admin-btn-secondary">Dashboard</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Event</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int) $r['ticket_details_id'] ?></td>
                        <td><code class="admin-slug"><?= $h((string) ($r['ticket_type'] ?? '')) ?></code></td>
                        <td><?= $h((string) ($r['category'] ?? '')) ?></td>
                        <td><?= $h((string) ($r['name'] ?? '')) ?></td>
                        <td class="admin-muted"><?= $h((string) ($r['event_title'] ?? '—')) ?></td>
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
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
