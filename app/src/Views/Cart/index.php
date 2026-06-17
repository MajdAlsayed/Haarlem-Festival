<?php
/** @var array $app */
/** @var \App\ViewModels\CartViewModel $vm */
/** @var string $cartCsrf */
/** @var ?string $success */
/** @var ?string $error */

$loggedIn = !empty($_SESSION['auth']['user_id'] ?? null);

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

    <?php if ($success): ?>
        <p class="copy-text cart-flash-success"><?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="cart-flash-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($vm->items === []): ?>
        <p class="copy-text cart-empty">No tickets yet. <a href="/tickets">Browse tickets</a></p>
    <?php else: ?>
        <div class="cart-lines">
            <?php foreach ($vm->items as $item): ?>
                <div class="festival-card cart-line">
                    <<div class="cart-line__content">
                        <div>
                            <strong class="section-subtitle cart-line__title"><?= htmlspecialchars((string)$item->name, ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="copy-text copy-text--sm copy-text--muted cart-line__meta">
                                Quantity <?= (int)$item->quantity ?>
                                <?php if ($item->isPayAsYouLike()): ?>
                                    <br>Pay as you like contribution: EUR <?= htmlspecialchars(number_format($item->getLineTotal(), 2), ENT_QUOTES, 'UTF-8') ?> total
                                <?php else: ?>
                                    x EUR <?= htmlspecialchars(number_format($item->price, 2), ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="cart-line__actions">
                            <span class="section-subtitle cart-line__price">EUR <?= htmlspecialchars(number_format($item->getLineTotal(), 2), ENT_QUOTES, 'UTF-8') ?></span>
                            <form method="post" action="/cart/remove">
                                <input type="hidden" name="cart_item_id" value="<?= (int)$item->cartItemId ?>">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$cartCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="btn btn--light btn--sm">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="section-subtitle cart-total">
            Total: EUR <?= htmlspecialchars(number_format($vm->total, 2), ENT_QUOTES, 'UTF-8') ?>
        </p>

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
