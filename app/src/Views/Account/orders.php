<?php
/** @var array $app */
/** @var list<array<string, mixed>> $list */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Settings for the page title, styles, body class
$pageTitle = 'My orders — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/account.css'];
$bodyClass = 'account-page account-orders-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'My orders', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="account-main container account-orders">
    <h1 class="section-title section-title--accent account-title">My orders</h1>

    <p class="copy-text copy-text--sm copy-text--muted account-lead">
        Open an order to see your invoice lines and ticket codes.
    </p>

    <?php if ($list === []): ?>
        <p class="copy-text account-empty">
            No orders yet. <a href="/tickets">Browse tickets</a>
        </p>
    <?php else: ?>
        <ul class="account-order-list">
            <?php foreach ($list as $row): ?>
                <?php
                $oid = (int) ($row['order_id'] ?? 0);
                ?>
                <li class="account-card account-order-row">
                    <a href="/account/order/<?= $oid ?>" class="account-order-link">
                        <span class="section-subtitle account-order-id">Order #<?= $h((string) $oid) ?></span>
                        <span class="copy-text copy-text--sm copy-text--muted account-order-meta">
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