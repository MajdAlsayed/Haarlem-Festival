<?php
/** @var array $app */
/** @var array<string, mixed> $order */
/** @var list<array{name:string,quantity:int,unit_price:string,line_total:string}> $lines */
/** @var list<array{ticket_code:string,item_name:string}> $tickets */
/** @var ?string $orderError */
/** @var string $checkoutCsrf */
/** @var bool $stripeOn */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$oid = (int) ($order['order_id'] ?? 0);
$status = (string) ($order['status'] ?? '');
$isPendingPayLater = $status === 'pending' && !empty($order['expires_at']);

if (!isset($orderError)) {
    $orderError = null;
}

if (!isset($checkoutCsrf)) {
    $checkoutCsrf = '';
}

if (!isset($stripeOn)) {
    $stripeOn = false;
}

// Settings for the page title, styles, body class
$pageTitle = 'Order #' . $oid . ' — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/account.css'];
$bodyClass = 'account-page account-invoice-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'My orders', 'url' => '/account/orders'],
        ['label' => 'Order #' . $oid, 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="account-main container account-invoice">
    <div class="account-page-actions no-print">
        <a href="/account/orders" class="btn btn--outline btn--sm">← My orders</a>
        <button type="button" class="btn btn--outline btn--sm" onclick="window.print()">Print invoice</button>
        <?php if ($status === 'paid'): ?>
            <a href="/account/order/<?= $h((string) $oid) ?>/invoice" class="btn btn--outline btn--sm" target="_blank" rel="noopener">Download invoice (PDF)</a>
            <a href="/account/order/<?= $h((string) $oid) ?>/tickets" class="btn btn--primary btn--sm" target="_blank" rel="noopener">Download tickets (PDF)</a>
        <?php endif; ?>
    </div>

    <div class="account-invoice-header">
        <h1 class="section-title section-title--accent account-title">Invoice &amp; tickets</h1>

        <p class="copy-text copy-text--sm copy-text--muted account-meta">
            <?= $h($app['site_name'] ?? 'Haarlem Festival') ?> — Order #<?= $h((string) $oid) ?><br>
            Status: <?= $h((string) ($order['status'] ?? '')) ?> ·
            Total: €<?= $h((string) ($order['total_amount'] ?? '0')) ?><br>
            <?php if ($isPendingPayLater): ?>
                Pay before: <?= $h((string) ($order['expires_at'] ?? '')) ?>
            <?php else: ?>
                Paid: <?= $h((string) ($order['paid_at'] ?? '—')) ?>
            <?php endif; ?>
        </p>
    </div>

    <?php if ($orderError !== null && $orderError !== ''): ?>
        <p class="account-error"><?= $h($orderError) ?></p>
    <?php endif; ?>

    <?php if ($isPendingPayLater): ?>
        <section class="account-card pending-payment-card">
            <h2 class="section-subtitle pending-payment-title">Complete payment</h2>

            <p class="copy-text copy-text--sm copy-text--muted">
                This order is reserved. Pay within 24 hours to receive ticket codes.
            </p>

            <?php if ($stripeOn && (float) ($order['total_amount'] ?? 0) > 0): ?>
                <form method="post" action="/checkout/pay-pending-stripe" class="pending-payment-form">
                    <input type="hidden" name="_csrf" value="<?= $h($checkoutCsrf) ?>">
                    <input type="hidden" name="order_id" value="<?= $h((string) $oid) ?>">
                    <button type="submit" class="btn btn--primary btn--block">Pay with card or iDEAL (Stripe)</button>
                </form>
            <?php endif; ?>

            <form method="post" action="/checkout/pay-pending">
                <input type="hidden" name="_csrf" value="<?= $h($checkoutCsrf) ?>">
                <input type="hidden" name="order_id" value="<?= $h((string) $oid) ?>">
                <button type="submit" class="btn btn--light btn--block">Confirm without payment (demo)</button>
            </form>
        </section>
    <?php endif; ?>

    <section class="account-section">

        <div class="festival-table-wrap">
            <table class="festival-table account-invoice-table">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Line</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($lines as $l): ?>
                    <tr>
                        <td><?= $h($l['name']) ?></td>
                        <td><?= $h((string) $l['quantity']) ?></td>
                        <td>€<?= $h($l['unit_price']) ?></td>
                        <td>€<?= $h($l['line_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="account-section account-ticket-section">
        <h2 class="section-subtitle account-section-title">Your tickets</h2>

        <?php if ($isPendingPayLater): ?>
            <p class="copy-text copy-text--sm copy-text--muted">Ticket codes appear here after payment is completed.</p>
        <?php elseif ($tickets === []): ?>
            <p class="copy-text copy-text--sm copy-text--muted">No ticket codes on file for this order.</p>
        <?php else: ?>
            <ul class="account-ticket-list">
                <?php foreach ($tickets as $t): ?>
                    <li class="account-card account-ticket-item">
                        <strong class="section-subtitle account-ticket-title"><?= $h($t['item_name']) ?></strong>
                        <span class="copy-text copy-text--sm account-ticket-code"><?= $h($t['ticket_code']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>