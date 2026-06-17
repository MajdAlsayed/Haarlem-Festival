<?php
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Settings for the page title, styles, body class
$pageTitle = 'My program — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/account.css'];
$bodyClass = 'account-page account-program-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'My program', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

<main class="account-main container account-program">
    <h1 class="section-title section-title--accent account-title">My program</h1>

    <p class="copy-text copy-text--sm copy-text--muted account-lead">
        Events you added to your cart while signed in. Buying tickets still uses the cart and checkout.
    </p>

    <?php if ($items === []): ?>
        <p class="copy-text account-empty">
            Nothing saved yet. <a href="/tickets">Browse tickets</a> and add something to your cart.
        </p>
    <?php else: ?>
        <ul class="account-program-list">
            <?php foreach ($items as $row): ?>
                <li class="account-card account-program-item">
                    <strong class="section-subtitle account-program-title"><?= $h($row['name']) ?></strong>

                    <?php if (!empty($row['event_title'])): ?>
                        <p class="copy-text copy-text--sm copy-text--muted account-program-meta">
                            <?= $h($row['event_title']) ?>
                            <?php if (!empty($row['event_day'])): ?>
                                · <?= $h($row['event_day']) ?>
                            <?php endif; ?>
                            <?php if (!empty($row['start_time'])): ?>
                                · <?= $h($row['start_time']) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <p class="copy-text copy-text--sm account-program-link">
                        <a href="/tickets">View on tickets page</a>
                    </p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>