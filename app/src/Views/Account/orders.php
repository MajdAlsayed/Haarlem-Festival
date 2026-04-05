<?php
/** @var array $app */
/** @var list<array<string, mixed>> $list */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My orders — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="tickets-page cart-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container" style="padding-top:2rem;max-width:720px;">
    <nav class="tickets-breadcrumbs" aria-label="Breadcrumb">
        <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
        <span class="tickets-bc-sep">›</span>
        <span>My orders</span>
    </nav>
    <h1 class="tickets-section-title">My orders</h1>
    <p class="tickets-card-sub">Open an order to see your invoice lines and ticket codes (assessment: receive tickets).</p>

    <?php if ($list === []): ?>
        <p class="tickets-card-sub">No orders yet. <a href="/tickets">Browse tickets</a></p>
    <?php else: ?>
        <ul class="account-order-list">
            <?php foreach ($list as $row): ?>
                <?php
                $oid = (int) ($row['order_id'] ?? 0);
                ?>
                <li class="account-order-row">
                    <a href="/account/order/<?= $oid ?>" class="account-order-link">
                        <span class="account-order-id">Order #<?= $h((string) $oid) ?></span>
                        <span class="account-order-meta">
                            <?= $h((string) ($row['status'] ?? '')) ?> ·
                            €<?= $h((string) ($row['total_amount'] ?? '0')) ?> ·
                            <?= $h((string) ($row['created_at'] ?? '')) ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
