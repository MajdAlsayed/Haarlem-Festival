<?php
/** @var array $app */
/** @var list<array<string,mixed>> $lines */
/** @var float $total */
/** @var ?string $success */
use App\Core\Csrf;

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$cartCsrf = Csrf::peek('cart') ?? Csrf::token('cart');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=1">
</head>
<body class="tickets-page tickets-page--jazz cart-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container" style="padding-top:2rem;">
    <h1 class="tickets-section-title">Your cart</h1>

    <?php if (!empty($success)): ?>
        <p class="tickets-flash-success"><?= $h($success) ?></p>
    <?php endif; ?>

    <?php if ($lines === []): ?>
        <p class="tickets-card-sub">No tickets yet. <a href="/tickets">Browse tickets</a></p>
    <?php else: ?>
        <div class="cart-lines">
            <?php foreach ($lines as $line): ?>
                <div class="tickets-card tickets-card--pass" style="margin-bottom:1rem;">
                    <div class="tickets-card-footer" style="border:none;padding:0;align-items:flex-start;">
                        <div>
                            <strong><?= $h($line['name']) ?></strong>
                            <p class="tickets-card-sub">Qty <?= (int) $line['qty'] ?> × €<?= $h(number_format((float) $line['unit'], 2)) ?></p>
                        </div>
                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <span class="tickets-price">€<?= $h(number_format((float) $line['line'], 2)) ?></span>
                            <form method="post" action="/cart/remove">
                                <input type="hidden" name="cart_item_id" value="<?= (int) $line['cart_item_id'] ?>">
                                <input type="hidden" name="_csrf" value="<?= $h($cartCsrf) ?>">
                                <button type="submit" class="tickets-btn-buy" style="background:transparent;border:1px solid currentColor;">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="tickets-price" style="font-size:1.25rem;margin-top:1rem;">Total: €<?= $h(number_format($total, 2)) ?></p>
        <?php
        $loggedIn = !empty($_SESSION['auth']['user_id'] ?? null);
        ?>
        <?php if ($loggedIn): ?>
            <p style="margin-top:1rem;"><a href="/checkout" class="tickets-btn-buy" style="display:inline-block;text-decoration:none;">Proceed to checkout</a></p>
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
