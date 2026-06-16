<?php
/** @var array $app */
/** @var array<string, mixed> $order */
/** @var list<array{ticket_code: string, item_name: string}> $ticketRows */
$h = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$oid = (int) ($order['order_id'] ?? 0);
$money = static fn(mixed $v): string => '€ ' . number_format(is_numeric($v) ? (float) $v : 0.0, 2, ',', '.');

$pageTitle = 'Order #' . $oid . ' tickets — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page admin-orders-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">
        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/orders">Orders</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Tickets #<?= $h((string) $oid) ?></span>
        </nav>

        <section class="admin-page-header admin-page-header-row">
            <div>
                <h1 class="admin-title">Ticket codes</h1>
                <p class="admin-subtitle">Order #<?= $h((string) $oid) ?> · <?= $h((string) ($order['status'] ?? '')) ?> · <?= $h($money($order['total_amount'] ?? 0)) ?></p>
            </div>
            <a href="/admin/scan" class="admin-btn admin-btn-primary">Open scanner</a>
        </section>

        <section class="admin-panel admin-orders-panel">
            <?php if ($ticketRows === []): ?>
                <div class="admin-empty-state">
                    <p class="admin-empty-title">No tickets</p>
                    <p class="admin-empty-text">This order has no ticket codes yet.</p>
                </div>
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
        </section>

        <p class="admin-back-row">
            <a href="/admin/orders/view?order_id=<?= $h((string) $oid) ?>" class="admin-btn admin-btn-secondary">View order</a>
            <a href="/admin/orders" class="admin-btn admin-btn-secondary">← All orders</a>
        </p>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
