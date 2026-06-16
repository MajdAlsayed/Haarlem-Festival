<?php
/** @var array<string, mixed> $app */
/** @var array<string, mixed> $order */
/** @var list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines */
/** @var list<array{ticket_code:string,item_name:string}> $tickets */

$h = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$oid = (int) ($order['order_id'] ?? 0);
$status = (string) ($order['status'] ?? '');
$name = trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? ''));
$money = static fn(mixed $v): string => '€ ' . number_format(is_numeric($v) ? (float) $v : 0.0, 2, ',', '.');
$statusClass = match ($status) {
    'paid' => 'admin-badge admin-badge-paid',
    'pending' => 'admin-badge admin-badge-pending',
    'canceled', 'cancelled' => 'admin-badge admin-badge-canceled',
    default => 'admin-badge admin-badge-inactive',
};

$pageTitle = 'Order #' . $oid . ' — ' . ($app['site_name'] ?? 'Haarlem Festival');
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
            <span>Order #<?= $h((string) $oid) ?></span>
        </nav>

        <section class="admin-page-header admin-page-header-row">
            <div>
                <h1 class="admin-title">Order #<?= $h((string) $oid) ?></h1>
                <p class="admin-subtitle">Order data, line items, and ticket access.</p>
            </div>
            <div class="admin-toolbar-actions">
                <a href="/admin/orders/tickets?order_id=<?= $h((string) $oid) ?>" class="admin-btn admin-btn-secondary">Ticket codes</a>
                <a href="/admin/orders/export" class="admin-btn admin-btn-primary admin-btn-export">
                    <span class="admin-btn-icon" aria-hidden="true">↓</span>
                    Export
                </a>
            </div>
        </section>

        <div class="admin-order-detail-grid">
            <section class="admin-panel admin-order-detail-card">
                <div class="admin-panel-top">
                    <h2 class="admin-section-heading">Customer</h2>
                </div>
                <dl class="admin-detail-list">
                    <div><dt>Name</dt><dd><?= $h($name !== '' ? $name : '—') ?></dd></div>
                    <div><dt>Email</dt><dd><?= $h((string) ($order['customer_email'] ?? '—')) ?></dd></div>
                    <div><dt>User ID</dt><dd><?= $h((string) ($order['user_id'] ?? '—')) ?></dd></div>
                </dl>
            </section>

            <section class="admin-panel admin-order-detail-card">
                <div class="admin-panel-top">
                    <h2 class="admin-section-heading">Payment</h2>
                </div>
                <dl class="admin-detail-list">
                    <div><dt>Status</dt><dd><span class="<?= $h($statusClass) ?>"><?= $h($status) ?></span></dd></div>
                    <div><dt>Total</dt><dd class="admin-money-cell"><?= $h($money($order['total_amount'] ?? 0)) ?></dd></div>
                    <div><dt>Paid at</dt><dd><?= $h((string) ($order['paid_at'] ?? '—')) ?></dd></div>
                    <div><dt>Created</dt><dd><?= $h((string) ($order['created_at'] ?? '—')) ?></dd></div>
                    <?php if (($order['expires_at'] ?? '') !== ''): ?>
                        <div><dt>Expires</dt><dd><?= $h((string) $order['expires_at']) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </section>
        </div>

        <section class="admin-panel admin-orders-panel admin-order-lines-panel">
            <div class="admin-panel-top">
                <h2 class="admin-section-heading">Line items</h2>
                <p class="admin-section-note"><?= $h((string) count($lines)) ?> item(s)</p>
            </div>
            <?php if ($lines === []): ?>
                <div class="admin-empty-state admin-empty-state--compact">
                    <p class="admin-empty-text">No line items for this order.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit price</th>
                            <th>Line total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lines as $line): ?>
                            <tr>
                                <td><?= $h($line['name']) ?></td>
                                <td><?= $h((string) $line['quantity']) ?></td>
                                <td class="admin-money-cell"><?= $h($money($line['unit_price'])) ?></td>
                                <td class="admin-money-cell"><?= $h($money($line['line_total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-panel admin-orders-panel">
            <div class="admin-panel-top">
                <h2 class="admin-section-heading">Tickets</h2>
                <p class="admin-section-note"><?= $h((string) count($tickets)) ?> code(s)</p>
            </div>
            <?php if ($tickets === []): ?>
                <div class="admin-empty-state admin-empty-state--compact">
                    <p class="admin-empty-text">No ticket codes yet (unpaid or not fulfilled).</p>
                </div>
            <?php else: ?>
                <ul class="admin-order-ticket-list admin-order-ticket-list--compact">
                    <?php foreach ($tickets as $tr): ?>
                        <li class="admin-order-ticket-item">
                            <strong><?= $h($tr['item_name']) ?></strong>
                            <code class="admin-scan-full-code admin-order-ticket-code"><?= $h($tr['ticket_code']) ?></code>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <p class="admin-back-row">
            <a href="/admin/orders" class="admin-btn admin-btn-secondary">← Back to orders</a>
        </p>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
