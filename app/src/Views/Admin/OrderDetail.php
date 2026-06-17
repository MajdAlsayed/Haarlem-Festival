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
            <span>Order #<?= $h((string) $vm->orderId) ?></span>
        </nav>

        <section class="admin-page-header admin-page-header-row">
            <div>
                <h1 class="admin-title">Order #<?= $h((string) $vm->orderId) ?></h1>
                <p class="admin-subtitle">Order data, line items, and ticket access.</p>
            </div>
            <div class="admin-toolbar-actions">
                <a href="/admin/orders/tickets?order_id=<?= $h((string) $vm->orderId) ?>" class="admin-btn admin-btn-secondary">Ticket codes</a>
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
                    <div><dt>Name</dt><dd><?= $h($vm->customerName) ?></dd></div>
                    <div><dt>Email</dt><dd><?= $h($vm->customerEmail) ?></dd></div>
                    <div><dt>User ID</dt><dd><?= $h($vm->userId) ?></dd></div>
                </dl>
            </section>

            <section class="admin-panel admin-order-detail-card">
                <div class="admin-panel-top">
                    <h2 class="admin-section-heading">Payment</h2>
                </div>
                <dl class="admin-detail-list">
                    <div><dt>Status</dt><dd><span class="<?= $h($vm->statusClass) ?>"><?= $h($vm->status) ?></span></dd></div>
                    <div><dt>Total</dt><dd class="admin-money-cell"><?= $h($vm->totalLabel) ?></dd></div>
                    <div><dt>Paid at</dt><dd><?= $h($vm->paidAt) ?></dd></div>
                    <div><dt>Created</dt><dd><?= $h($vm->createdAt) ?></dd></div>
                    <?php if ($vm->expiresAt !== ''): ?>
                        <div><dt>Expires</dt><dd><?= $h($vm->expiresAt) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </section>
        </div>

        <section class="admin-panel admin-orders-panel admin-order-lines-panel">
            <div class="admin-panel-top">
                <h2 class="admin-section-heading">Line items</h2>
                <p class="admin-section-note"><?= $h((string) count($vm->lineRows)) ?> item(s)</p>
            </div>
            <?php if ($vm->lineRows === []): ?>
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
                        <?php foreach ($vm->lineRows as $line): ?>
                            <tr>
                                <td><?= $h($line['name']) ?></td>
                                <td><?= $h($line['quantity']) ?></td>
                                <td class="admin-money-cell"><?= $h($line['unitPrice']) ?></td>
                                <td class="admin-money-cell"><?= $h($line['lineTotal']) ?></td>
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
                <p class="admin-section-note"><?= $h((string) count($vm->ticketRows)) ?> code(s)</p>
            </div>
            <?php if ($vm->ticketRows === []): ?>
                <div class="admin-empty-state admin-empty-state--compact">
                    <p class="admin-empty-text">No ticket codes yet (unpaid or not fulfilled).</p>
                </div>
            <?php else: ?>
                <ul class="admin-order-ticket-list admin-order-ticket-list--compact">
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
            <a href="/admin/orders" class="admin-btn admin-btn-secondary">← Back to orders</a>
        </p>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
