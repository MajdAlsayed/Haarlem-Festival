<?php
/** Dance event detail page for /dance/event/{id}. */
/** @var \App\ViewModels\EventDetailViewModel $viewModel */

use App\Core\Csrf;

$event = $viewModel->event;
$appSettings = $viewModel->appSettings;
$dateTimeLine = $viewModel->formattedDate ? ($viewModel->formattedDate . ' • ' . $viewModel->startTime) : $viewModel->startTime;
$h = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
// Return users to this event after adding a ticket from POST /cart/add.
$cartReturn = '/dance/event/' . (int) $event->id . '#tickets';
$cartFormCsrf = Csrf::peek('cart') ?? Csrf::token('cart');
// Keep ticket price formatting centralized (including free tickets).
$formatTicketEur = static function (mixed $price, bool $isFree): string {
    if ($isFree) {
        return 'Free';
    }
    $n = is_numeric($price) ? (float) $price : 0.0;

    return '€ ' . number_format($n, 2, ',', '.');
};

// Ticket descriptions are stored as newline-separated bullets; render as list.
$renderFeatureList = static function (string $desc) use ($h): void {
    $lines = preg_split('/\r\n|\r|\n/', trim($desc));
    $lines = array_values(array_filter(array_map('trim', $lines), static fn ($l) => $l !== ''));
    if ($lines === []) {
        return;
    }
    echo '<ul class="event-detail-ticket-features-list event-detail-ticket-features-list--figma">';
    foreach ($lines as $line) {
        echo '<li>' . $h($line) . '</li>';
    }
    echo '</ul>';
};

// Festival pass uses a denser two-column feature layout.
$renderFeatureListTwoCol = static function (string $desc) use ($h): void {
    $lines = preg_split('/\r\n|\r|\n/', trim($desc));
    $lines = array_values(array_filter(array_map('trim', $lines), static fn ($l) => $l !== ''));
    if ($lines === []) {
        return;
    }
    $mid = (int) ceil(count($lines) / 2);
    $col1 = array_slice($lines, 0, $mid);
    $col2 = array_slice($lines, $mid);
    echo '<div class="event-detail-ticket-features-cols">';
    echo '<ul class="event-detail-ticket-features-list event-detail-ticket-features-list--figma">';
    foreach ($col1 as $line) {
        echo '<li>' . $h($line) . '</li>';
    }
    echo '</ul>';
    if ($col2 !== []) {
        echo '<ul class="event-detail-ticket-features-list event-detail-ticket-features-list--figma">';
        foreach ($col2 as $line) {
            echo '<li>' . $h($line) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
};

$hasEventTickets = $viewModel->eventTickets !== [];
$hasDayPass = $viewModel->danceDayPass !== null;
$hasFestivalPass = $viewModel->danceAllAccessPass !== null;
$hasAnyTicketOption = $hasEventTickets || $hasDayPass || $hasFestivalPass;
$ticketsFigmaTitle = 'Ticket for ' . $event->title . ' in ' . ($event->venueName ?? '');
// Grid span flags keep top row balanced when one ticket tier is missing.
$figmaSpanStandardTop = $hasEventTickets && !$hasDayPass;
$figmaSpanDayTop = !$hasEventTickets && $hasDayPass;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($appSettings['css_version'] ?? '1') ?>">
</head>
<body class="dance-page event-detail-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
$cartToastMsg = '';
$cartToastError = false;
if (!empty($viewModel->cartFlashSuccess)) {
    $cartToastMsg = (string) $viewModel->cartFlashSuccess;
} elseif (!empty($viewModel->cartFlashError)) {
    $cartToastMsg = (string) $viewModel->cartFlashError;
    $cartToastError = true;
}
?>
<?php if ($cartToastMsg !== ''): ?>
<div id="cartAddToast" class="event-detail-cart-toast<?= $cartToastError ? ' event-detail-cart-toast--error' : '' ?>" role="status" aria-live="polite" aria-atomic="true">
    <span class="event-detail-cart-toast-icon" aria-hidden="true"><?= $cartToastError ? '!' : '✓' ?></span>
    <span class="event-detail-cart-toast-msg"><?= $h($cartToastMsg) ?></span>
    <a href="/cart" class="event-detail-cart-toast-link">View cart</a>
    <button type="button" class="event-detail-cart-toast-close" aria-label="Dismiss notification">&times;</button>
</div>
<script>
(function () {
    var el = document.getElementById('cartAddToast');
    if (!el) return;
    var close = function () {
        el.classList.add('event-detail-cart-toast--hide');
        setTimeout(function () { if (el && el.parentNode) el.parentNode.removeChild(el); }, 320);
    };
    el.querySelector('.event-detail-cart-toast-close')?.addEventListener('click', close);
    setTimeout(close, 7000);
})();
</script>
<?php endif; ?>

<main>
    <section class="event-detail-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 40%, rgba(0,0,0,0.6) 100%), url('<?= htmlspecialchars($viewModel->heroImage) ?>');">
        <div class="event-detail-hero-overlay"></div>
        <div class="event-detail-hero-content">
            <p class="event-detail-hero-venue"><?= htmlspecialchars($event->venueName) ?></p>
            <h1 class="event-detail-hero-title">
                <span class="title-orange"><?= htmlspecialchars($viewModel->eventSubtitle) ?></span>
            </h1>
            <a href="#event-info" class="event-detail-hero-btn">More info <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>

    <nav class="breadcrumbs dance-breadcrumbs event-detail-breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($viewModel->breadcrumbs as $i => $crumb): ?>
            <?php if ($i > 0): ?><span class="breadcrumb-sep">›</span><?php endif; ?>
            <?php if (!empty($crumb['url'])): ?>
                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
            <?php else: ?>
                <span class="breadcrumb-current"><?= htmlspecialchars($crumb['label']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <div class="event-detail-group event-detail-group--panel event-detail-group--info">
    <section id="event-info" class="event-detail-section event-detail-section--grouped" aria-labelledby="event-info-heading">
        <div class="event-detail-overview">
            <?php if (isset($viewModel->galleryImages[0])): ?>
            <div class="event-detail-overview-image">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[0]) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.parentElement.style.display='none'">
            </div>
            <?php endif; ?>
            <div class="event-detail-overview-info">
                <p class="event-detail-group-label">Event</p>
                <h2 class="event-detail-group-title" id="event-info-heading">Event Information</h2>
                <ul class="event-detail-info-list">
                    <li><div class="icon-wrap"><img src="/images/icons/dateIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Date &amp; Time</span><span class="value"><?= htmlspecialchars($dateTimeLine) ?></span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Location</span><span class="value"><?= htmlspecialchars($event->venueName . ($event->venueCity ? ', ' . $event->venueCity : '')) ?></span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/musicIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Genre</span><span class="value">Dance</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/artistIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Artist(s)</span><span class="value"><?= htmlspecialchars($viewModel->artistsDisplay) ?></span></div></li>
                </ul>
            </div>
        </div>
    </section>
    </div>

    <div class="event-detail-group event-detail-group--panel event-detail-group--about">
    <section class="event-detail-section event-detail-section--grouped" aria-labelledby="about-desc-heading">
        <div class="event-detail-features">
            <div class="event-detail-desc-text">
                <p class="event-detail-group-label">About</p>
                <h2 class="event-detail-group-title" id="about-desc-heading">Description</h2>
                <div class="event-detail-desc-paragraphs"><?= nl2br(htmlspecialchars($event->description ?? '')) ?></div>
                <h3 class="event-detail-features-heading">This event features</h3>
                <ul>
                    <li><img src="/images/icons/musicIcon.png" alt="" class="feat-icon" aria-hidden="true"> World-class electronic DJs</li>
                    <li><img src="/images/icons/SoundIcon.png" alt="" class="feat-icon" aria-hidden="true"> High-quality sound</li>
                    <li><img src="/images/icons/lightIcon.png" alt="" class="feat-icon" aria-hidden="true"> Laser and light shows</li>
                    <li><img src="/images/icons/eyeIcon.png" alt="" class="feat-icon" aria-hidden="true"> Immersive visual design</li>
                    <li><img src="/images/icons/webIcon.png" alt="" class="feat-icon" aria-hidden="true"> Unforgettable atmosphere</li>
                </ul>
            </div>
            <?php if (isset($viewModel->galleryImages[1])): ?>
            <div class="event-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[1]) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'">
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="event-detail-section event-detail-section--grouped event-detail-section--group-split" aria-labelledby="about-expect-heading">
        <div class="event-detail-features">
            <?php if (isset($viewModel->galleryImages[2])): ?>
            <div class="event-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[2]) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'">
            </div>
            <?php endif; ?>
            <div class="event-detail-desc-text">
                <p class="event-detail-group-label">About</p>
                <h2 class="event-detail-group-title" id="about-expect-heading">What to Expect</h2>
                <ul class="event-detail-expect-list">
                    <li>Top electronic and dance artists</li>
                    <li>Premium sound and lighting</li>
                    <li>High-energy atmosphere</li>
                    <li>Unique venue experience</li>
                </ul>
            </div>
        </div>
    </section>
    </div>

    <div class="event-detail-group event-detail-group--tickets">
    <section id="tickets" class="event-detail-section event-detail-section-alt event-detail-tickets-section--figma" aria-labelledby="tickets-heading">
        <p class="event-detail-group-label event-detail-group-label--tickets">Tickets</p>
        <h2 class="event-detail-tickets-figma-title" id="tickets-heading"><?= $h($ticketsFigmaTitle) ?></h2>
        <div class="event-detail-tickets event-detail-tickets-layout">
            <?php if (!$hasAnyTicketOption): ?>
                <p class="event-detail-tickets-empty">Tickets for this event are not available online yet. Browse all dance tickets or check back soon.</p>
                <div class="event-detail-tickets-cta event-detail-tickets-cta--figma">
                    <a href="/tickets?cat=dance" class="event-detail-tickets-all-btn">Browse dance tickets</a>
                </div>
            <?php else: ?>
                <div class="event-detail-tickets-grid event-detail-tickets-grid--figma">
                    <?php if ($hasEventTickets): ?>
                    <div class="event-detail-tickets-cell<?= $figmaSpanStandardTop ? ' event-detail-tickets-cell--span-top' : '' ?>">
                        <?php
                        $eventCount = count($viewModel->eventTickets);
                        foreach ($viewModel->eventTickets as $t):
                            $tdId = (int) ($t['ticket_details_id'] ?? 0);
                            $tName = (string) ($t['name'] ?? 'Ticket');
                            $tDesc = isset($t['description']) && $t['description'] !== null && $t['description'] !== '' ? (string) $t['description'] : '';
                            $isFree = !empty($t['is_free']);
                            // VIP naming controls both visual accent and badge rendering.
                            $vipClass = (stripos($tName, 'VIP') !== false) ? ' vip' : '';
                            $pst = $t['stock'] ?? null;
                            // If only one non-VIP ticket exists, present as "Standard Ticket".
                            $cardTitle = ($eventCount === 1 && stripos($tName, 'VIP') === false) ? 'Standard Ticket' : $tName;
                            ?>
                        <article class="event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--flex<?= $vipClass ?>">
                            <?php if (stripos($tName, 'VIP') !== false): ?>
                                <span class="event-detail-ticket-badge event-detail-ticket-badge--vip-figma">VIP</span>
                            <?php endif; ?>
                            <div class="event-detail-ticket-card-head">
                                <h3 class="event-detail-ticket-title"><?= $h($cardTitle) ?></h3>
                                <p class="event-detail-ticket-price"><?= $h($formatTicketEur($t['price'] ?? 0, $isFree)) ?></p>
                            </div>
                            <?php if (is_array($pst) && !empty($pst['sold_out'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold out</p>
                            <?php elseif (is_array($pst) && !empty($pst['nearly'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost sold out</p>
                            <?php elseif (is_array($pst) && !empty($pst['low_stock'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--low">Only <?= $h((string) (int) ($pst['remaining'] ?? 0)) ?> left</p>
                            <?php endif; ?>
                            <?php if ($tDesc !== ''): ?>
                                <?php $renderFeatureList($tDesc); ?>
                            <?php else: ?>
                                <p class="event-detail-ticket-features event-detail-ticket-features--figma">Access to this event</p>
                            <?php endif; ?>
                            <?php if ($tdId > 0 && (!is_array($pst) || empty($pst['sold_out']))): ?>
                            <!-- Submit to cart with fixed quantity=1; cart page handles edits. -->
                            <form method="post" action="/cart/add" class="event-detail-cart-form">
                                <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                <input type="hidden" name="ticket_details_id" value="<?= $tdId ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="return" value="<?= $h($cartReturn) ?>">
                                <button type="submit" class="event-detail-ticket-buy-figma">Buy tickets</button>
                            </form>
                            <?php elseif ($tdId > 0 && is_array($pst) && !empty($pst['sold_out'])): ?>
                                <p class="event-detail-cart-form"><span class="event-detail-ticket-buy-figma event-detail-ticket-buy-figma--disabled" aria-disabled="true">Sold out</span></p>
                            <?php endif; ?>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($hasDayPass):
                        $p = $viewModel->danceDayPass;
                        $tdId = (int) ($p['ticket_details_id'] ?? 0);
                        $pName = (string) ($p['name'] ?? 'Day Pass');
                        $pDesc = (string) ($p['description'] ?? '');
                        $isFree = !empty($p['is_free']);
                        $pst = $p['stock'] ?? null;
                        ?>
                    <div class="event-detail-tickets-cell<?= $figmaSpanDayTop ? ' event-detail-tickets-cell--span-top' : '' ?>">
                        <article class="event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--pass event-detail-ticket-card--flex">
                            <div class="event-detail-ticket-card-head">
                                <h3 class="event-detail-ticket-title"><?= $h($pName) ?></h3>
                                <p class="event-detail-ticket-price"><?= $h($formatTicketEur($p['price'] ?? 0, $isFree)) ?></p>
                            </div>
                            <?php if (is_array($pst) && !empty($pst['sold_out'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold out</p>
                            <?php elseif (is_array($pst) && !empty($pst['nearly'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost sold out</p>
                            <?php elseif (is_array($pst) && !empty($pst['low_stock'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--low">Only <?= $h((string) (int) ($pst['remaining'] ?? 0)) ?> left</p>
                            <?php endif; ?>
                            <?php if ($pDesc !== ''): ?>
                                <?php $renderFeatureList($pDesc); ?>
                            <?php endif; ?>
                            <?php if (($p['pass_day'] ?? '') !== '' || ($p['pass_time'] ?? '') !== ''): ?>
                            <!-- Optional schedule metadata for day pass, shown only when configured. -->
                            <p class="event-detail-pass-meta event-detail-pass-meta--figma">
                                <?php if (($p['pass_day'] ?? '') !== ''): ?>
                                    <span><?= $h(ucfirst((string) $p['pass_day'])) ?> pass</span>
                                <?php endif; ?>
                                <?php if (($p['pass_time'] ?? '') !== ''): ?>
                                    <span class="event-detail-pass-meta-sep"> · </span><span><?= $h((string) $p['pass_time']) ?></span>
                                <?php endif; ?>
                            </p>
                            <?php endif; ?>
                            <?php if ($tdId > 0 && (!is_array($pst) || empty($pst['sold_out'])) && !$isFree): ?>
                            <form method="post" action="/cart/add" class="event-detail-cart-form">
                                <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                <input type="hidden" name="ticket_details_id" value="<?= $tdId ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="return" value="<?= $h($cartReturn) ?>">
                                <button type="submit" class="event-detail-ticket-buy-figma">Buy tickets</button>
                            </form>
                            <?php elseif ($tdId > 0 && is_array($pst) && !empty($pst['sold_out'])): ?>
                                <p class="event-detail-cart-form"><span class="event-detail-ticket-buy-figma event-detail-ticket-buy-figma--disabled" aria-disabled="true">Sold out</span></p>
                            <?php endif; ?>
                        </article>
                    </div>
                    <?php endif; ?>

                    <?php if ($hasFestivalPass):
                        $p = $viewModel->danceAllAccessPass;
                        $tdId = (int) ($p['ticket_details_id'] ?? 0);
                        $pName = (string) ($p['name'] ?? 'All-Access Pass');
                        $pDesc = (string) ($p['description'] ?? '');
                        $isFree = !empty($p['is_free']);
                        $pst = $p['stock'] ?? null;
                        ?>
                    <div class="event-detail-tickets-cell event-detail-tickets-cell--full">
                        <article class="event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--festival event-detail-ticket-card--flex">
                            <span class="event-detail-ticket-badge event-detail-ticket-badge--best">BEST VALUE</span>
                            <div class="event-detail-ticket-card-head">
                                <h3 class="event-detail-ticket-title"><?= $h($pName) ?></h3>
                                <p class="event-detail-ticket-price"><?= $h($formatTicketEur($p['price'] ?? 0, $isFree)) ?></p>
                            </div>
                            <?php if (is_array($pst) && !empty($pst['sold_out'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold out</p>
                            <?php elseif (is_array($pst) && !empty($pst['nearly'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost sold out</p>
                            <?php elseif (is_array($pst) && !empty($pst['low_stock'])): ?>
                                <p class="event-detail-stock-badge event-detail-stock-badge--low">Only <?= $h((string) (int) ($pst['remaining'] ?? 0)) ?> left</p>
                            <?php endif; ?>
                            <?php if ($pDesc !== ''): ?>
                                <?php $renderFeatureListTwoCol($pDesc); ?>
                            <?php endif; ?>
                            <?php if (($p['schedule_display'] ?? '') !== ''): ?>
                                <!-- CMS-provided schedule string for all-access pass. -->
                                <p class="event-detail-pass-meta event-detail-pass-meta--figma"><?= $h((string) $p['schedule_display']) ?></p>
                            <?php endif; ?>
                            <?php if ($tdId > 0 && (!is_array($pst) || empty($pst['sold_out'])) && !$isFree): ?>
                            <form method="post" action="/cart/add" class="event-detail-cart-form">
                                <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                <input type="hidden" name="ticket_details_id" value="<?= $tdId ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="return" value="<?= $h($cartReturn) ?>">
                                <button type="submit" class="event-detail-ticket-buy-figma">Buy tickets</button>
                            </form>
                            <?php elseif ($tdId > 0 && is_array($pst) && !empty($pst['sold_out'])): ?>
                                <p class="event-detail-cart-form"><span class="event-detail-ticket-buy-figma event-detail-ticket-buy-figma--disabled" aria-disabled="true">Sold out</span></p>
                            <?php endif; ?>
                        </article>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="event-detail-tickets-cta event-detail-tickets-cta--figma">
                    <a href="/cart" class="event-detail-book-figma-btn">Book now</a>
                    <a href="/tickets?cat=dance" class="event-detail-tickets-all-btn">All dance tickets</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    </div>

    <div class="event-detail-group event-detail-group--panel event-detail-group--location">
    <section class="event-detail-location event-detail-location--grouped" aria-labelledby="location-heading">
        <div class="event-detail-location-inner">
            <p class="event-detail-group-label">Location</p>
            <h2 class="event-detail-group-title event-detail-location__title" id="location-heading"><?= htmlspecialchars($viewModel->locationDisplay) ?></h2>
            <p class="event-detail-location-hint">Easy to reach by public transport or by car</p>
            <div class="event-detail-map">
                <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $viewModel->mapLon - 0.02 ?>%2C<?= $viewModel->mapLat - 0.015 ?>%2C<?= $viewModel->mapLon + 0.02 ?>%2C<?= $viewModel->mapLat + 0.015 ?>&layer=mapnik&marker=<?= $viewModel->mapLat ?>%2C<?= $viewModel->mapLon ?>" width="100%" height="100%" loading="lazy" title="Map of <?= htmlspecialchars($event->venueName) ?>"></iframe>
            </div>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= htmlspecialchars($viewModel->mapQuery) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-white">Open in Google Maps</a>
        </div>
    </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
