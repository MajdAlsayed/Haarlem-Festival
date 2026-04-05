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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order #<?= $h((string) $oid) ?> — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="tickets-page cart-page account-invoice-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container account-invoice" style="padding-top:2rem;max-width:640px;">
    <p class="no-print" style="margin-bottom:1rem;">
        <a href="/account/orders">← My orders</a>
        ·
        <button type="button" class="tickets-btn-buy" style="border:none;cursor:pointer;" onclick="window.print()">Print invoice</button>
    </p>

    <header class="account-invoice-header">
        <h1 class="tickets-section-title">Invoice &amp; tickets</h1>
        <p class="tickets-card-sub">
            <?= $h($app['site_name'] ?? 'Haarlem Festival') ?> — Order #<?= $h((string) $oid) ?><br>
            Status: <?= $h((string) ($order['status'] ?? '')) ?> ·
            Total: €<?= $h((string) ($order['total_amount'] ?? '0')) ?><br>
            <?php if ($isPendingPayLater): ?>
                Pay before: <?= $h((string) ($order['expires_at'] ?? '')) ?>
            <?php else: ?>
                Paid: <?= $h((string) ($order['paid_at'] ?? '—')) ?>
            <?php endif; ?>
        </p>
    </header>

    <?php if ($orderError !== null && $orderError !== ''): ?>
        <p class="tickets-flash-success" style="border-color:rgba(220,53,69,0.5);background:rgba(220,53,69,0.15);color:#f5a5ad;"><?= $h($orderError) ?></p>
    <?php endif; ?>

    <?php if ($isPendingPayLater): ?>
        <section style="margin-top:1rem;padding:1rem;border-radius:8px;border:1px solid rgba(245,180,0,0.35);background:rgba(245,180,0,0.08);">
            <h2 class="tickets-section-title" style="font-size:1.05rem;">Complete payment</h2>
            <p class="tickets-card-sub">This order is reserved. Pay within 24 hours to receive ticket codes.</p>
            <?php if ($stripeOn && (float) ($order['total_amount'] ?? 0) > 0): ?>
                <form method="post" action="/checkout/pay-pending-stripe" style="margin-bottom:0.75rem;">
                    <input type="hidden" name="_csrf" value="<?= $h($checkoutCsrf) ?>">
                    <input type="hidden" name="order_id" value="<?= $h((string) $oid) ?>">
                    <button type="submit" class="tickets-btn-buy" style="width:100%;">Pay with card or iDEAL (Stripe)</button>
                </form>
            <?php endif; ?>
            <form method="post" action="/checkout/pay-pending">
                <input type="hidden" name="_csrf" value="<?= $h($checkoutCsrf) ?>">
                <input type="hidden" name="order_id" value="<?= $h((string) $oid) ?>">
                <button type="submit" class="tickets-btn-buy" style="width:100%;background:transparent;border:1px solid rgba(245,180,0,0.6);color:#f5b400;">
                    Confirm without payment (demo)
                </button>
            </form>
        </section>
    <?php endif; ?>

    <section style="margin-top:1.5rem;">
        <h2 class="tickets-section-title" style="font-size:1.1rem;">Line items</h2>
        <table class="account-invoice-table">
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
    </section>

    <section style="margin-top:1.5rem;">
        <h2 class="tickets-section-title" style="font-size:1.1rem;">Your tickets</h2>
        <?php if ($isPendingPayLater): ?>
            <p class="tickets-card-sub">Ticket codes appear here after payment is completed.</p>
        <?php elseif ($tickets === []): ?>
            <p class="tickets-card-sub">No ticket codes on file for this order.</p>
        <?php else: ?>
            <ul class="checkout-ticket-list" style="list-style:none;padding:0;margin:0;">
                <?php foreach ($tickets as $t): ?>
                    <li class="tickets-card tickets-card--pass" style="margin-bottom:0.5rem;padding:0.75rem;font-family:monospace;font-size:0.9rem;">
                        <strong><?= $h($t['item_name']) ?></strong><br>
                        <span><?= $h($t['ticket_code']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
