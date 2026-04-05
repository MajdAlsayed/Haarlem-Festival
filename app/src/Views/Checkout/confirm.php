<?php
/** @var array $app */
/** @var \App\ViewModels\CartViewModel $vm */
/** @var ?string $error */
/** @var string $csrf */
/** @var bool $stripeOn */
/** @var bool $demoOn */

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=1">
</head>
<body class="tickets-page cart-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container" style="padding-top:2rem;max-width:640px;">
    <h1 class="tickets-section-title">Checkout</h1>

    <?php if (!empty($error)): ?>
        <p class="tickets-flash-success" style="border-color:rgba(220,53,69,0.5);background:rgba(220,53,69,0.15);color:#f5a5ad;"><?= $h($error) ?></p>
    <?php endif; ?>

    <div class="cart-lines" style="margin-bottom:1.5rem;">
        <?php foreach ($vm->items as $item): ?>
            <div class="tickets-card tickets-card--pass" style="margin-bottom:0.75rem;">
                <div class="tickets-card-footer" style="border:none;padding:0.75rem;justify-content:space-between;">
                    <div>
                        <strong><?= $h($item->name) ?></strong>
                        <p class="tickets-card-sub">Qty <?= (int) $item->quantity ?> × €<?= $h(number_format($item->price, 2)) ?></p>
                    </div>
                    <span class="tickets-price">€<?= $h(number_format($item->getLineTotal(), 2)) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="tickets-price" style="font-size:1.25rem;margin-bottom:1.5rem;">Total: €<?= $h(number_format($vm->total, 2)) ?></p>

    <?php if ($stripeOn): ?>
        <form method="post" action="/checkout/pay-stripe" style="margin-bottom:1rem;">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <button type="submit" class="tickets-btn-buy" style="font-size:1rem;padding:0.75rem 1.5rem;width:100%;">
                Pay with card or iDEAL (Stripe — test mode)
            </button>
        </form>
        <p class="tickets-card-sub" style="margin-bottom:1rem;">You will leave this site to complete payment on Stripe’s secure page.</p>
    <?php endif; ?>

    <form method="post" action="/checkout/pay-later" style="margin-bottom:1.25rem;padding-top:0.5rem;border-top:1px solid rgba(255,255,255,0.08);">
        <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
        <button type="submit" class="tickets-btn-buy" style="font-size:0.95rem;padding:0.65rem 1.25rem;width:100%;background:transparent;border:1px solid rgba(255,255,255,0.35);color:inherit;">
            Pay later (24h)
        </button>
        <p class="tickets-card-sub" style="margin-top:0.5rem;font-size:0.85rem;">Reserves your cart as an unpaid order. You will receive email (and a reminder) and must pay within 24 hours or the reservation is cancelled.</p>
    </form>

    <?php if ($demoOn): ?>
        <?php if ($stripeOn): ?>
            <p class="tickets-card-sub" style="margin:1rem 0 0.5rem;font-size:0.85rem;opacity:0.9;">Optional — class demo (no real charge):</p>
        <?php else: ?>
            <p class="tickets-card-sub" style="margin-bottom:0.75rem;">
                Add <code>STRIPE_SECRET_KEY</code> and <code>APP_PUBLIC_URL</code> in the environment for real card/iDEAL (Stripe test keys).
            </p>
        <?php endif; ?>
        <form method="post" action="/checkout/pay">
            <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
            <button type="submit" class="tickets-btn-buy" style="font-size:0.95rem;padding:0.6rem 1.2rem;background:transparent;border:1px solid rgba(245,180,0,0.6);color:#f5b400;">
                Confirm without payment (demo)
            </button>
        </form>
    <?php endif; ?>

    <p class="tickets-card-sub" style="margin-top:1rem;"><a href="/cart">Back to cart</a></p>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
