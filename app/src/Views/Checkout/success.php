<?php
/** @var array $app */
/** @var array<string,mixed> $order */
/** @var list<array<string,string>> $tickets */
/** @var bool $paidWithStripe */

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order confirmed — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=1">
</head>
<body class="tickets-page cart-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container" style="padding-top:2rem;max-width:640px;">
    <h1 class="tickets-section-title">Thank you</h1>
    <p class="tickets-card-sub">
        Order #<?= $h((string) $order['order_id']) ?> — paid €<?= $h(number_format((float) $order['total_amount'], 2)) ?>
        <?php if (!empty($paidWithStripe)): ?>
            <span> (Stripe)</span>
        <?php else: ?>
            <span> (demo payment)</span>
        <?php endif; ?>
    </p>

    <section style="margin-top:1.5rem;">
        <h2 class="tickets-section-title" style="font-size:1.1rem;">Your tickets</h2>
        <?php if ($tickets === []): ?>
            <p class="tickets-card-sub">No ticket rows (unexpected).</p>
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

    <p class="tickets-card-sub" style="margin-top:1.5rem;">
        <?php if (!empty($paidWithStripe)): ?>
            Payment was processed by Stripe. A confirmation was sent to your account email (and appended to <code>app/storage/mail/orders.log</code> in local Docker). Use these codes at the entrance.
        <?php else: ?>
            Demo flow: no money was charged. Confirmation is logged under <code>app/storage/mail/orders.log</code> and you can reopen this invoice anytime from <a href="/account/orders">My orders</a>.
        <?php endif; ?>
    </p>
    <p><a href="/tickets" class="tickets-btn-buy" style="display:inline-block;text-decoration:none;">Browse more tickets</a></p>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
