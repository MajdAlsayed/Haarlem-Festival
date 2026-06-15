<?php
/** @var array $app */
/** @var list<array<string,mixed>> $lines */
/** @var float $total */
/** @var ?string $success */
use App\Core\Csrf;

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$cartCsrf = Csrf::peek('cart') ?? Csrf::token('cart');

// Settings for the page title, styles, body class
$pageTitle = 'Cart — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/pages/cart.css'];
$bodyClass = 'cart-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Cart', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="cart-main container">
    <h1 class="section-title section-title--accent section-title--underlined cart-title">Your cart</h1>

    <?php if (!empty($success)): ?>
        <p class="copy-text cart-flash-success"><?= $h($success) ?></p>
    <?php endif; ?>

    <?php if ($lines === []): ?>
        <p class="copy-text cart-empty">No tickets yet. <a href="/tickets">Browse tickets</a></p>
    <?php else: ?>
        <div class="cart-lines">
            <?php foreach ($lines as $line): ?>
                <div class="festival-card cart-line">
                    <div class="cart-line__content">
                        <div>
                            <strong class="section-subtitle cart-line__title"><?= $h($line['name']) ?></strong>
                            <p class="copy-text copy-text--sm copy-text--muted cart-line__meta">
                                Qty <?= (int) $line['qty'] ?> × €<?= $h(number_format((float) $line['unit'], 2)) ?>
                            </p>
                        </div>

                        <div class="cart-line__actions">
                            <span class="section-subtitle cart-line__price">
                                €<?= $h(number_format((float) $line['line'], 2)) ?>
                            </span>

                            <form method="post" action="/cart/remove">
                                <input type="hidden" name="cart_item_id" value="<?= (int) $line['cart_item_id'] ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($cartCsrf) ?>">
                                <button type="submit" class="btn btn--light btn--sm">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="section-subtitle cart-total">
            Total: €<?= $h(number_format($total, 2)) ?>
        </p>

        <?php
        $loggedIn = !empty($_SESSION['auth']['user_id'] ?? null);
        ?>

        <?php if ($loggedIn): ?>
            <p class="cart-action">
                <a href="/checkout" class="btn btn--primary">Proceed to checkout</a>
            </p>
        <?php else: ?>
            <p class="copy-text cart-action">
                <a href="/login?return=/checkout">Log in</a> to complete your purchase.
            </p>
        <?php endif; ?>

        <p class="copy-text copy-text--sm cart-continue">
            <a href="/tickets">Continue shopping</a>
        </p>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>