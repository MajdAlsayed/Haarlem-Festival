<?php
/** @var array $app */
/** @var \App\ViewModels\CartViewModel $vm */
/** @var string $cartCsrf */
/** @var ?string $success */
/** @var ?string $error */

$loggedIn = !empty($_SESSION['auth']['user_id'] ?? null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart - <?= htmlspecialchars((string)($app['site_name'] ?? 'Haarlem Festival'), ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars((string)($app['css_version'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=1">
</head>
<body class="tickets-page tickets-page--jazz cart-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container" style="padding-top:2rem;">
    <h1 class="tickets-section-title">Your cart</h1>

    <?php if ($success): ?>
        <p class="tickets-flash-success"><?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="tickets-flash-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($vm->items === []): ?>
        <p class="tickets-card-sub">No tickets yet. <a href="/tickets">Browse tickets</a></p>
    <?php else: ?>
        <div class="cart-lines">
            <?php foreach ($vm->items as $item): ?>
                <div class="tickets-card tickets-card--pass" style="margin-bottom:1rem;">
                    <div class="tickets-card-footer" style="border:none;padding:0;align-items:flex-start;">
                        <div>
                            <strong><?= htmlspecialchars((string)$item->name, ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="tickets-card-sub">
                                Quantity <?= (int)$item->quantity ?>
                                <?php if ($item->isPayAsYouLike()): ?>
                                    <br>Pay as you like contribution: EUR <?= htmlspecialchars(number_format($item->getLineTotal(), 2), ENT_QUOTES, 'UTF-8') ?> total
                                <?php else: ?>
                                    x EUR <?= htmlspecialchars(number_format($item->price, 2), ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <span class="tickets-price">EUR <?= htmlspecialchars(number_format($item->getLineTotal(), 2), ENT_QUOTES, 'UTF-8') ?></span>
                            <form method="post" action="/cart/remove">
                                <input type="hidden" name="cart_item_id" value="<?= (int)$item->cartItemId ?>">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)$cartCsrf, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit" class="tickets-btn-buy" style="background:transparent;border:1px solid currentColor;">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="tickets-price" style="font-size:1.25rem;margin-top:1rem;">
            Total: EUR <?= htmlspecialchars(number_format($vm->total, 2), ENT_QUOTES, 'UTF-8') ?>
        </p>

        <?php if ($loggedIn): ?>
            <p style="margin-top:1rem;">
                <a href="/checkout" class="tickets-btn-buy" style="display:inline-block;text-decoration:none;">Proceed to checkout</a>
            </p>
        <?php else: ?>
            <p class="tickets-card-sub" style="margin-top:1rem;">
                <a href="/login?return=/checkout">Log in</a> to complete your purchase.
            </p>
        <?php endif; ?>

        <p class="tickets-card-sub"><a href="/tickets">Continue shopping</a></p>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
