<?php
/** @var array $app */
/** @var list<array<string,mixed>> $events */
$success = \App\Core\Session::getFlash('admin_success');
$error = \App\Core\Session::getFlash('admin_error');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jazz events — Admin — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <span>Events</span>
        </nav>

        <h1 class="admin-title">Jazz events</h1>

        <?php if ($success): ?>
            <div class="admin-alert admin-alert-success"><?= $h($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert-error"><?= $h($error) ?></div>
        <?php endif; ?>

        <p>
            <a href="/admin/jazz/events/new" class="admin-btn admin-btn-primary">Add event</a>
            <a href="/admin/jazz" class="admin-btn admin-btn-secondary">Back to Jazz CMS</a>
        </p>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Title</th>
                        <th>Venue</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                    <tr>
                        <td><?= $h($e['event_day'] ?? '') ?></td>
                        <td><?= $h(trim(($e['start_time'] ?? '') . ($e['end_time'] ? '–' . $e['end_time'] : ''))) ?></td>
                        <td><?= $h($e['title'] ?? '') ?></td>
                        <td><?= $h(($e['venue_name'] ?? '') . ', ' . ($e['venue_city'] ?? '')) ?></td>
                        <td>
                            <a href="/admin/jazz/events/edit?id=<?= (int) $e['event_id'] ?>" class="admin-btn admin-btn-sm admin-btn-primary">Edit</a>
                            <?php
                            $delForm = 'admin_jazz_del_' . (int) $e['event_id'];
                            $delTok = \App\Core\Csrf::token($delForm);
                            ?>
                            <form method="post" action="/admin/jazz/events/delete" style="display:inline;" onsubmit="return confirm('Delete this event?');">
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

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
