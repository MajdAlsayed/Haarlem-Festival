<?php
/** @var array $app */
/** @var array<string,mixed> $order */
/** @var list<array<string,string>> $tickets */
/** @var bool $paidWithStripe */

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Settings for the page title, styles, body class
$pageTitle = 'Order confirmed — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/pages/cart.css'];
$bodyClass = 'cart-page checkout-success-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Cart', 'url' => '/cart'],
        ['label' => 'Checkout', 'url' => '/checkout'],
        ['label' => 'Order confirmed', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="cart-main container checkout-success-main">
    <div class="success-icon">✓</div>

    <h1 class="section-title section-title--accent cart-title">Thank you</h1>

    <p class="copy-text copy-text--sm copy-text--muted checkout-success-summary">
        Order #<?= $h((string) $order['order_id']) ?> — paid €<?= $h(number_format((float) $order['total_amount'], 2)) ?>
        <?php if (!empty($paidWithStripe)): ?>
            <span> (Stripe)</span>
        <?php else: ?>
            <span> (demo payment)</span>
        <?php endif; ?>
    </p>

    <section class="checkout-section checkout-ticket-section">
        <h2 class="section-subtitle checkout-section-title">Your tickets</h2>

        <?php if ($tickets === []): ?>
            <p class="copy-text copy-text--sm copy-text--muted">No ticket rows found.</p>
        <?php else: ?>
            <ul class="checkout-ticket-list">
                <?php foreach ($tickets as $t): ?>
                    <li class="checkout-ticket-item">
                        <strong class="section-subtitle checkout-ticket-title"><?= $h($t['item_name']) ?></strong>
                        <span class="copy-text copy-text--sm checkout-ticket-code"><?= $h($t['ticket_code']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <p class="copy-text copy-text--sm copy-text--muted checkout-success-note">
        <?php if (!empty($paidWithStripe)): ?>
            Payment was processed by Stripe. A confirmation was sent to your account email and logged locally.
            Use these codes at the entrance.
        <?php else: ?>
            Demo flow: no money was charged. Confirmation is logged locally and you can reopen this invoice anytime from
            <a href="/account/orders">My orders</a>.
        <?php endif; ?>
    </p>

    <p class="cart-action">
        <a href="/tickets" class="btn btn--primary">Browse more tickets</a>
    </p>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>