<?php
/** @var array $app */
/** @var list<array{name:string,event_title:?string,event_day:?string,start_time:?string,ticket_details_id:int}> $items */
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My program — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=<?= $h($app['css_version'] ?? '1') ?>">
</head>
<body class="tickets-page cart-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main container" style="padding-top:2rem;max-width:720px;">
    <nav class="tickets-breadcrumbs" aria-label="Breadcrumb">
        <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
        <span class="tickets-bc-sep">›</span>
        <span>My program</span>
    </nav>
    <h1 class="tickets-section-title">My program</h1>
    <p class="tickets-card-sub">Events you added to your cart while signed in (your personal shortlist). Buying tickets still uses the cart and checkout.</p>

    <?php if ($items === []): ?>
        <p class="tickets-card-sub">Nothing saved yet. <a href="/tickets">Browse tickets</a> and add something to your cart.</p>
    <?php else: ?>
        <ul class="account-order-list" style="list-style:none;padding:0;margin:0;">
            <?php foreach ($items as $row): ?>
                <li class="tickets-card tickets-card--pass" style="margin-bottom:0.75rem;padding:1rem;">
                    <strong><?= $h($row['name']) ?></strong>
                    <?php if (!empty($row['event_title'])): ?>
                        <p class="tickets-card-sub" style="margin:0.35rem 0 0 0;">
                            <?= $h($row['event_title']) ?>
                            <?php if (!empty($row['event_day'])): ?>
                                · <?= $h($row['event_day']) ?>
                            <?php endif; ?>
                            <?php if (!empty($row['start_time'])): ?>
                                · <?= $h($row['start_time']) ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    <p class="tickets-card-sub" style="margin:0.5rem 0 0 0;">
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
