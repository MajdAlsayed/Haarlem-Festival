<?php
// Each ticket row may include `stock` (sold_out, nearly, low_stock, remaining) from TicketAvailabilityService for badges and hiding BUY.
/** @var array $app */
/** @var string $category */
/** @var string $intro */
/** @var list<array<string,mixed>> $passes */
/** @var array<string, list<array<string,mixed>>> $byDay */
/** @var ?string $cartFlash */
/** @var ?string $cartFlashError */

use App\Core\Csrf;
use App\Repositories\TicketsRepository;

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$cartFormCsrf = Csrf::peek('cart') ?? Csrf::token('cart');
$dayLabel = static fn(string $d): string => ucfirst(strtolower(trim($d)));

$tabs = TicketsRepository::CATEGORIES;
$bodyClass = 'tickets-page tickets-page--' . preg_replace('/[^a-z]/', '', $category);

$dayPasses = array_values(array_filter($passes, static fn ($p) => ($p['ticket_type'] ?? '') === 'day_pass'));
$allAccessPasses = array_values(array_filter($passes, static fn ($p) => ($p['ticket_type'] ?? '') === 'all_access_pass'));

/** Day / all-access bundles only for Jazz & Dance (not History or Stories). */
$showSpecialOffers = !in_array($category, ['history', 'stories'], true);

$dayOrder = ['thursday', 'friday', 'saturday', 'sunday'];

$returnUrl = '/tickets?cat=' . rawurlencode($category);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tickets — <?= $h($app['site_name'] ?? 'Haarlem Festival') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/tickets.css?v=1">
</head>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="tickets-main">
    <section class="tickets-hero container">
        <?php if (!empty($cartFlash)): ?>
            <p class="tickets-flash-success"><?= $h($cartFlash) ?></p>
        <?php endif; ?>
        <?php if (!empty($cartFlashError)): ?>
            <p class="tickets-flash-success" style="border-color:rgba(220,53,69,0.5);background:rgba(220,53,69,0.15);color:#f5a5ad;"><?= $h($cartFlashError) ?></p>
        <?php endif; ?>
        <nav class="tickets-breadcrumbs" aria-label="Breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="tickets-bc-sep">›</span>
            <span>Tickets</span>
        </nav>
        <p class="tickets-hero-lead"><?= $h($intro) ?></p>

        <div class="tickets-tabs" role="tablist" aria-label="Ticket category">
            <?php foreach ($tabs as $tab):
                $active = $tab === $category;
                ?>
                <a href="/tickets?cat=<?= $h(rawurlencode($tab)) ?>"
                   class="tickets-tab<?= $active ? ' is-active' : '' ?>"
                   role="tab"
                   <?= $active ? 'aria-selected="true"' : 'aria-selected="false"' ?>><?= $h(strtoupper($tab)) ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($showSpecialOffers && ($dayPasses !== [] || $allAccessPasses !== [])): ?>
    <section class="tickets-section container">
        <h2 class="tickets-section-title">Special offer</h2>
        <div class="tickets-grid">
            <?php foreach ($dayPasses as $p): ?>
                <article class="tickets-card tickets-card--pass">
                    <h3 class="tickets-card-title"><?= $h($p['name']) ?></h3>
                    <?php
                    $pst = $p['stock'] ?? null;
                    if (is_array($pst) && !empty($pst['sold_out'])): ?>
                        <p class="tickets-stock-badge tickets-stock-badge--soldout">Sold out</p>
                    <?php elseif (is_array($pst) && !empty($pst['nearly'])): ?>
                        <p class="tickets-stock-badge tickets-stock-badge--nearly">Almost sold out</p>
                    <?php elseif (is_array($pst) && !empty($pst['low_stock'])): ?>
                        <p class="tickets-stock-badge tickets-stock-badge--low">Only <?= $h((string) (int) ($pst['remaining'] ?? 0)) ?> left</p>
                    <?php endif; ?>
                    <p class="tickets-card-sub"><?= $h($p['description']) ?></p>
                    <div class="tickets-card-meta">
                        <?php if (($p['pass_day'] ?? '') !== ''): ?>
                            <div><span class="tickets-meta-label">Day</span><span class="tickets-meta-value"><?= $h($dayLabel((string) $p['pass_day'])) ?></span></div>
                        <?php endif; ?>
                        <?php if (($p['pass_time'] ?? '') !== ''): ?>
                            <div><span class="tickets-meta-label">Time</span><span class="tickets-meta-value"><?= $h((string) $p['pass_time']) ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="tickets-card-footer">
                        <span class="tickets-price"><?= !empty($p['is_free']) ? 'FREE' : '€' . $h(number_format((float) $p['price'], 0)) ?></span>
                        <?php if (empty($p['is_free']) && empty(($p['stock']['sold_out'] ?? false))): ?>
                            <form method="post" action="/cart/add" class="tickets-buy-form">
                                <input type="hidden" name="ticket_details_id" value="<?= (int) $p['ticket_details_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                <input type="hidden" name="return" value="<?= $h($returnUrl) ?>">
                                <button type="submit" class="tickets-btn-buy">BUY ›</button>
                            </form>
                        <?php elseif (empty($p['is_free'])): ?>
                            <span class="tickets-btn-buy tickets-btn-buy--disabled" aria-disabled="true">Sold out</span>
                        <?php else: ?>
                            <span class="tickets-btn-buy tickets-btn-buy--disabled" aria-disabled="true">FREE</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php foreach ($allAccessPasses as $p): ?>
                <article class="tickets-card tickets-card--pass tickets-card--wide">
                    <h3 class="tickets-card-title"><?= $h($p['name']) ?></h3>
                    <?php
                    $pst = $p['stock'] ?? null;
                    if (is_array($pst) && !empty($pst['sold_out'])): ?>
                        <p class="tickets-stock-badge tickets-stock-badge--soldout">Sold out</p>
                    <?php elseif (is_array($pst) && !empty($pst['nearly'])): ?>
                        <p class="tickets-stock-badge tickets-stock-badge--nearly">Almost sold out</p>
                    <?php elseif (is_array($pst) && !empty($pst['low_stock'])): ?>
                        <p class="tickets-stock-badge tickets-stock-badge--low">Only <?= $h((string) (int) ($pst['remaining'] ?? 0)) ?> left</p>
                    <?php endif; ?>
                    <p class="tickets-card-sub"><?= $h($p['description']) ?></p>
                    <div class="tickets-card-meta">
                        <?php if (($p['schedule_display'] ?? '') !== ''): ?>
                            <div class="tickets-meta-full">
                                <span class="tickets-meta-label">Days</span>
                                <span class="tickets-meta-value"><?= $h((string) $p['schedule_display']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="tickets-card-footer">
                        <span class="tickets-price"><?= !empty($p['is_free']) ? 'FREE' : '€' . $h(number_format((float) $p['price'], 0)) ?></span>
                        <?php if (empty($p['is_free']) && empty(($p['stock']['sold_out'] ?? false))): ?>
                            <form method="post" action="/cart/add" class="tickets-buy-form">
                                <input type="hidden" name="ticket_details_id" value="<?= (int) $p['ticket_details_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                <input type="hidden" name="return" value="<?= $h($returnUrl) ?>">
                                <button type="submit" class="tickets-btn-buy">BUY ›</button>
                            </form>
                        <?php elseif (empty($p['is_free'])): ?>
                            <span class="tickets-btn-buy tickets-btn-buy--disabled" aria-disabled="true">Sold out</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php foreach ($dayOrder as $dayKey):
        $items = $byDay[$dayKey] ?? [];
        if ($items === []) {
            continue;
        }
        ?>
        <section class="tickets-section container">
            <h2 class="tickets-section-title"><?= $h($dayLabel($dayKey)) ?></h2>
            <div class="tickets-grid">
                <?php foreach ($items as $e):
                    $start = $e['start_time'] ?? '';
                    $end = $e['end_time'] ?? '';
                    $timeStr = $start !== '' && $end !== '' ? $start . ' – ' . $end : ($start !== '' ? $start : '—');
                    $venueLine = $e['subtitle'] ?? '';
                    ?>
                    <article class="tickets-card">
                        <h3 class="tickets-card-title"><?= $h($e['name']) ?></h3>
                        <?php
                        $est = $e['stock'] ?? null;
                        if (is_array($est) && !empty($est['sold_out'])): ?>
                            <p class="tickets-stock-badge tickets-stock-badge--soldout">Sold out</p>
                        <?php elseif (is_array($est) && !empty($est['nearly'])): ?>
                            <p class="tickets-stock-badge tickets-stock-badge--nearly">Almost sold out</p>
                        <?php elseif (is_array($est) && !empty($est['low_stock'])): ?>
                            <p class="tickets-stock-badge tickets-stock-badge--low">Only <?= $h((string) (int) ($est['remaining'] ?? 0)) ?> left</p>
                        <?php endif; ?>
                        <?php if ($venueLine !== ''): ?>
                            <p class="tickets-card-venue"><?= $h($venueLine) ?></p>
                        <?php endif; ?>
                        <div class="tickets-card-meta">
                            <div><span class="tickets-meta-label">Day</span><span class="tickets-meta-value"><?= $h($dayLabel((string) ($e['event_day'] ?? ''))) ?></span></div>
                            <div><span class="tickets-meta-label">Time</span><span class="tickets-meta-value"><?= $h($timeStr) ?></span></div>
                        </div>
                        <div class="tickets-card-footer">
                            <span class="tickets-price"><?= !empty($e['is_free']) ? 'FREE' : '€' . $h(number_format((float) $e['price'], 0)) ?></span>
                            <?php if (empty($e['is_free']) && empty(($e['stock']['sold_out'] ?? false))): ?>
                                <form method="post" action="/cart/add" class="tickets-buy-form">
                                    <input type="hidden" name="ticket_details_id" value="<?= (int) $e['ticket_details_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                    <input type="hidden" name="return" value="<?= $h($returnUrl) ?>">
                                    <button type="submit" class="tickets-btn-buy">BUY ›</button>
                                </form>
                            <?php elseif (empty($e['is_free'])): ?>
                                <span class="tickets-btn-buy tickets-btn-buy--disabled" aria-disabled="true">Sold out</span>
                            <?php else: ?>
                                <span class="tickets-btn-buy tickets-btn-buy--disabled" aria-disabled="true">FREE</span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
