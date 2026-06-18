<?php

$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = $vm->pageTitle;
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
            <a href="/"><?= $h($vm->siteName) ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/orders">Orders</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Tickets #<?= $h((string) $vm->orderId) ?></span>
        </nav>

        <section class="admin-page-header admin-page-header-row">
            <div>
                <h1 class="admin-title">Ticket codes</h1>
                <p class="admin-subtitle">Order #<?= $h((string) $vm->orderId) ?> · <?= $h($vm->status) ?> · <?= $h($vm->totalLabel) ?></p>
            </div>
            <a href="/admin/scan" class="admin-btn admin-btn-primary">Open scanner</a>
        </section>

        <section class="admin-panel admin-orders-panel">
            <?php if ($vm->ticketRows === []): ?>
                <div class="admin-empty-state">
                    <p class="admin-empty-title">No tickets</p>
                    <p class="admin-empty-text">This order has no ticket codes yet.</p>
                </div>
            <?php else: ?>
                <ul class="admin-order-ticket-list">
                    <?php foreach ($vm->ticketRows as $ticket): ?>
                        <li class="admin-order-ticket-item">
                            <strong><?= $h($ticket['itemName']) ?></strong>
                            <code class="admin-scan-full-code admin-order-ticket-code"><?= $h($ticket['code']) ?></code>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <p class="admin-back-row">
            <a href="/admin/orders/view?order_id=<?= $h((string) $vm->orderId) ?>" class="admin-btn admin-btn-secondary">View order</a>
            <a href="/admin/orders" class="admin-btn admin-btn-secondary">← All orders</a>
        </p>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
