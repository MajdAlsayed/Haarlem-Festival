<?php
/** @var array $app */
/** @var array<string, mixed> $order */
/** @var list<array{ticket_code: string, item_name: string}> $ticketRows */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$oid = (int) ($order['order_id'] ?? 0);

$pageTitle = 'Order #' . $oid . ' tickets — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
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

        <?php if ($ticketRows === []): ?>
            <p class="admin-hint">No ticket rows for this order.</p>
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

        <p class="admin-lead admin-order-back-link">
            <a href="/admin/orders">← Back to orders</a>
        </p>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>