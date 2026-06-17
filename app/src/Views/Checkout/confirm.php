<?php
/** @var array $app */
/** @var \App\ViewModels\CartViewModel $vm */
/** @var ?string $error */
/** @var string $csrf */
/** @var bool $stripeOn */

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Settings for the page title, styles, body class
$pageTitle = 'Checkout — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/pages/cart.css'];
$bodyClass = 'cart-page checkout-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Cart', 'url' => '/cart'],
        ['label' => 'Checkout', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="cart-main container checkout-main">
    <h1 class="section-title section-title--accent cart-title">Checkout</h1>

    <?php if (!empty($error)): ?>
        <p class="cart-flash-error"><?= $h($error) ?></p>
    <?php endif; ?>

    <section class="checkout-section">
        <h2 class="section-subtitle checkout-section-title">Order summary</h2>

        <div class="cart-lines checkout-lines">
            <?php foreach ($vm->items as $item): ?>
                <div class="cart-line checkout-line">
                    <div class="cart-line__content">
                        <div>
                            <strong class="section-subtitle cart-line__title"><?= $h($item->name) ?></strong>
                            <p class="copy-text copy-text--sm copy-text--muted cart-line__meta">
                                Qty <?= (int) $item->quantity ?> × €<?= $h(number_format($item->price, 2)) ?>
                            </p>
                        </div>

                        <span class="section-subtitle cart-line__price">
                            €<?= $h(number_format($item->getLineTotal(), 2)) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="section-subtitle cart-total checkout-total">
            Total: €<?= $h(number_format($vm->total, 2)) ?>
        </p>
    </section>

    <section class="checkout-section checkout-payment">
        <h2 class="section-subtitle checkout-section-title">Payment</h2>

        <?php if ($stripeOn): ?>
            <form method="post" action="/checkout/pay-stripe" class="checkout-form">
                <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
                <button type="submit" class="btn btn--primary btn--block">
                    Pay with card or iDEAL
                </button>
            </form>

            <p class="copy-text copy-text--sm copy-text--muted checkout-note">
                You will leave this site to complete payment on Stripe's secure page.
            </p>
        <?php endif; ?>

        <form method="post" action="/checkout/pay-later" class="checkout-form checkout-form--separated">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <button type="submit" class="btn btn--light btn--block">
                Pay later (24h)
            </button>

            <p class="copy-text copy-text--sm copy-text--muted checkout-note">
                Reserves your cart as an unpaid order. You will receive email and must pay within 24 hours or the reservation is cancelled.
            </p>
        </form>
    </section>

    <p class="copy-text copy-text--sm cart-continue">
        <a href="/cart">Back to cart</a>
    </p>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>