<?php
/** @var array $app */
/** @var list<array<string,mixed>> $events */
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'Dance events — Admin — ' . ($app['site_name'] ?? 'Haarlem Festival');
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
            <span>Events</span>
        </nav>

        <h1 class="admin-title">Dance events</h1>
        <p class="admin-hint" style="margin-bottom:1rem;">All dance events. Capacity controls ticket availability for each event.</p>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <p>
            <a href="/admin/dance/events/new" class="admin-btn admin-btn-primary">Add event</a>
            <a href="/admin/dance" class="admin-btn admin-btn-secondary">Back to Dance CMS</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Title</th>
                        <th>Venue</th>
                        <th>Capacity</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                    <tr>
                        <td><?= $h($e['event_day'] ?? '') ?></td>
                        <td><?= $h(trim(($e['start_time'] ?? '') . (!empty($e['end_time']) ? '–' . $e['end_time'] : ''))) ?></td>
                        <td><?= $h($e['title'] ?? '') ?></td>
                        <td><?= $h(($e['venue_name'] ?? '') . ', ' . ($e['venue_city'] ?? '')) ?></td>
                        <td class="admin-muted"><?= isset($e['seats']) && $e['seats'] !== null ? $h((string) (int) $e['seats']) : '—' ?></td>
                        <td>
                            <a href="/admin/dance/events/edit?id=<?= (int) $e['event_id'] ?>" class="admin-btn admin-btn-sm admin-btn-primary">Edit</a>
                            <?php
                            $delForm = 'admin_dance_del_' . (int) $e['event_id'];
                            $delTok = \App\Core\Csrf::token($delForm);
                            ?>
                            <form method="post" action="/admin/dance/events/delete" style="display:inline;" onsubmit="return confirm('Delete this event?');">
                                <input type="hidden" name="_csrf_form" value="<?= $h($delForm) ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($delTok) ?>">
                                <input type="hidden" name="event_id" value="<?= (int) $e['event_id'] ?>">
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

<?php require __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
