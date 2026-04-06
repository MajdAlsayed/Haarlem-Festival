<?php
/** @var array $app */
/** @var array<string, mixed> $order */
/** @var list<array{ticket_code: string, item_name: string}> $ticketRows */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$oid = (int) ($order['order_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #<?= $h((string) $oid) ?> tickets — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
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
            <a href="/admin/orders">Orders</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Order #<?= $h((string) $oid) ?></span>
        </nav>

        <h1 class="admin-title">Ticket codes — order #<?= $h((string) $oid) ?></h1>
        <p class="admin-lead">
            Status <?= $h((string) ($order['status'] ?? '')) ?>,
            total €<?= $h((string) ($order['total_amount'] ?? '0')) ?>.
            Copy a code for testing the <a href="/admin/scan">scanner</a>.
        </p>

        <?php require __DIR__ . '/partials/admin_nav.php'; ?>

        <?php if ($ticketRows === []): ?>
            <p>No ticket rows for this order.</p>
        <?php else: ?>
            <ul class="admin-order-ticket-list">
                <?php foreach ($ticketRows as $tr): ?>
                    <li class="admin-order-ticket-item">
                        <strong><?= $h($tr['item_name']) ?></strong>
                        <code class="admin-scan-full-code admin-order-ticket-code"><?= $h($tr['ticket_code']) ?></code>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p class="admin-lead" style="margin-top:1.5rem;"><a href="/admin/orders">← Back to orders</a></p>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
