<?php
/** Dance event detail page for /dance/event/{id}. */

/** @var \App\ViewModels\EventDetailViewModel $vm */

use App\Core\Csrf;

$breadcrumbs = $vm->breadcrumbs;
$event = $vm->event;
$appSettings = $vm->appSettings;

$h = static fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

// small readers so we don't repeat isset checks for every ticket field
$text = static function (array $row, string $key): string {
    if (isset($row[$key]) && $row[$key] !== null) {
        return (string) $row[$key];
    }
    return '';
};
$intVal = static function (array $row, string $key): int {
    if (isset($row[$key])) {
        return (int) $row[$key];
    }
    return 0;
};
$stockOf = static function (array $row): array {
    if (isset($row['stock']) && is_array($row['stock'])) {
        return $row['stock'];
    }
    return [];
};

// price label, including free tickets
$formatTicketEur = static function (mixed $price, bool $isFree): string {
    if ($isFree) {
        return 'Free';
    }
    $amount = 0.0;
    if (is_numeric($price)) {
        $amount = (float) $price;
    }
    return '€ ' . number_format($amount, 2, ',', '.');
};

// ticket descriptions are newline-separated bullets; render as a list
$renderFeatureList = static function (string $desc) use ($h): void {
    $lines = preg_split('/\r\n|\r|\n/', trim($desc));
    $lines = array_values(array_filter(array_map('trim', $lines), static fn($l) => $l !== ''));
    if ($lines === []) {
        return;
    }
    echo '<ul class="event-detail-ticket-features-list event-detail-ticket-features-list--figma">';
    foreach ($lines as $line) {
        echo '<li>' . $h($line) . '</li>';
    }
    echo '</ul>';
};

// festival pass uses a denser two-column feature layout
$renderFeatureListTwoCol = static function (string $desc) use ($h): void {
    $lines = preg_split('/\r\n|\r|\n/', trim($desc));
    $lines = array_values(array_filter(array_map('trim', $lines), static fn($l) => $l !== ''));
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

// date line falls back to just the start time when there's no formatted date
$dateTimeLine = $vm->startTime;
if (!empty($vm->formattedDate)) {
    $dateTimeLine = $vm->formattedDate . ' • ' . $vm->startTime;
}

// send users back to this event after they add a ticket from POST /cart/add
$cartReturn = '/dance/event/' . (int) $event->id . '#tickets';
$cartFormCsrf = Csrf::peek('cart');
if ($cartFormCsrf === null) {
    $cartFormCsrf = Csrf::token('cart');
}

// location line for the info list (city is optional)
$venueLine = $event->venueName;
if ((string) $event->venueCity !== '') {
    $venueLine .= ', ' . $event->venueCity;
}

$hasEventTickets = $vm->eventTickets !== [];
$hasDayPass = $vm->danceDayPass !== null;
$hasFestivalPass = $vm->danceAllAccessPass !== null;
$hasAnyTicketOption = $hasEventTickets || $hasDayPass || $hasFestivalPass;
$ticketsFigmaTitle = 'Ticket for ' . $event->title . ' in ' . (string) $event->venueName;

// keep the top row balanced when one ticket tier is missing
$standardCellClass = 'event-detail-tickets-cell';
if ($hasEventTickets && !$hasDayPass) {
    $standardCellClass .= ' event-detail-tickets-cell--span-top';
}
$dayCellClass = 'event-detail-tickets-cell';
if (!$hasEventTickets && $hasDayPass) {
    $dayCellClass .= ' event-detail-tickets-cell--span-top';
}

// page title, styles, body class
$pageTitle = $vm->pageTitle;
$pageStyles = ['/css/pages/dance.css'];
$bodyClass = 'dance-page event-detail-page';

// hero values read by the page-hero partial
$pageHeroTitle = $event->title;
if ((string) $event->venueName !== '') {
    $pageHeroTitle = $event->venueName;
}
$pageHeroSubtitle = $vm->eventSubtitle;
$pageHeroImage = $vm->heroImage;
$pageHeroAlt = $event->title;
$pageHeroClass = 'dance-detail-hero dance-event-detail-hero';
$pageHeroContentClass = 'dance-detail-hero__content dance-event-detail-hero__content';
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
// show one cart toast: success first, otherwise an error
$cartToastMsg = '';
$cartToastError = false;
if (!empty($vm->cartFlashSuccess)) {
    $cartToastMsg = (string) $vm->cartFlashSuccess;
} elseif (!empty($vm->cartFlashError)) {
    $cartToastMsg = (string) $vm->cartFlashError;
    $cartToastError = true;
}
$cartToastClass = 'event-detail-cart-toast';
$cartToastIcon = '✓';
if ($cartToastError) {
    $cartToastClass .= ' event-detail-cart-toast--error';
    $cartToastIcon = '!';
}
?>
<?php if ($cartToastMsg !== ''): ?>
    <div id="cartAddToast"
         class="<?= $cartToastClass ?>" role="status"
         aria-live="polite" aria-atomic="true">
        <span class="event-detail-cart-toast-icon" aria-hidden="true"><?= $cartToastIcon ?></span>
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
                setTimeout(function () {
                    if (el && el.parentNode) el.parentNode.removeChild(el);
                }, 320);
            };
            el.querySelector('.event-detail-cart-toast-close')?.addEventListener('click', close);
            setTimeout(close, 7000);
        })();
    </script>
<?php endif; ?>

<main>
    <!-- Hero -->
    <?php require __DIR__ . '/../partials/page-hero.php'; ?>

    <!-- Breadcrumbs nav -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <div class="event-detail-group event-detail-group--panel event-detail-group--info">
        <section id="event-info" class="event-detail-section event-detail-section--grouped"
                 aria-labelledby="event-info-heading">
            <div class="event-detail-overview">
                <?php if (isset($vm->galleryImages[0])): ?>
                    <div class="event-detail-overview-image">
                        <img src="/images/dance/<?= htmlspecialchars($vm->galleryImages[0]) ?>"
                             alt="<?= htmlspecialchars($event->title) ?>"
                             onerror="this.parentElement.style.display='none'">
                    </div>
                <?php endif; ?>
                <div class="event-detail-overview-info">
                    <p class="event-detail-group-label">Event</p>
                    <h2 class="section-title section-title--accent event-detail-group-title" id="event-info-heading">Event Information</h2>
                    <ul class="event-detail-info-list">
                        <li>
                            <div class="icon-wrap"><img src="/images/icons/dateIcon.png" alt="" class="icon"
                                                        aria-hidden="true"></div>
                            <div class="event-detail-info-item"><span class="label">Date &amp; Time</span><span
                                        class="value"><?= htmlspecialchars($dateTimeLine) ?></span></div>
                        </li>
                        <li>
                            <div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon"
                                                        aria-hidden="true"></div>
                            <div class="event-detail-info-item"><span class="label">Location</span><span
                                        class="value"><?= htmlspecialchars($venueLine) ?></span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-wrap"><img src="/images/icons/musicIcon.png" alt="" class="icon"
                                                        aria-hidden="true"></div>
                            <div class="event-detail-info-item"><span class="label">Genre</span><span class="value">Dance</span>
                            </div>
                        </li>
                        <li>
                            <div class="icon-wrap"><img src="/images/icons/artistIcon.png" alt="" class="icon"
                                                        aria-hidden="true"></div>
                            <div class="event-detail-info-item"><span class="label">Artist(s)</span><span
                                        class="value"><?= htmlspecialchars($vm->artistsDisplay) ?></span></div>
                        </li>
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
                    <h2 class="section-title section-title--accent event-detail-group-title" id="about-desc-heading">Description</h2>
                    <div class="copy-text event-detail-desc-paragraphs"><?= nl2br(htmlspecialchars((string) $event->description)) ?></div>
                    <h3 class="event-detail-features-heading">This event features</h3>
                    <ul>
                        <li><img src="/images/icons/musicIcon.png" alt="" class="feat-icon" aria-hidden="true">
                            World-class electronic DJs
                        </li>
                        <li><img src="/images/icons/SoundIcon.png" alt="" class="feat-icon" aria-hidden="true">
                            High-quality sound
                        </li>
                        <li><img src="/images/icons/lightIcon.png" alt="" class="feat-icon" aria-hidden="true"> Laser
                            and light shows
                        </li>
                        <li><img src="/images/icons/eyeIcon.png" alt="" class="feat-icon" aria-hidden="true"> Immersive
                            visual design
                        </li>
                        <li><img src="/images/icons/webIcon.png" alt="" class="feat-icon" aria-hidden="true">
                            Unforgettable atmosphere
                        </li>
                    </ul>
                </div>
                <?php if (isset($vm->galleryImages[1])): ?>
                    <div class="event-detail-desc-image">
                        <img src="/images/dance/<?= htmlspecialchars($vm->galleryImages[1]) ?>"
                             alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'">
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="event-detail-section event-detail-section--grouped event-detail-section--group-split"
                 aria-labelledby="about-expect-heading">
            <div class="event-detail-features">
                <?php if (isset($vm->galleryImages[2])): ?>
                    <div class="event-detail-desc-image">
                        <img src="/images/dance/<?= htmlspecialchars($vm->galleryImages[2]) ?>"
                             alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'">
                    </div>
                <?php endif; ?>
                <div class="event-detail-desc-text">
                    <p class="event-detail-group-label">About</p>
                    <h2 class="section-title section-title--accent event-detail-group-title" id="about-expect-heading">What to Expect</h2>
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
        <section id="tickets" class="event-detail-section event-detail-section-alt event-detail-tickets-section--figma"
                 aria-labelledby="tickets-heading">
            <p class="event-detail-group-label event-detail-group-label--tickets">Tickets</p>
            <h2 class="event-detail-tickets-figma-title" id="tickets-heading"><?= $h($ticketsFigmaTitle) ?></h2>
            <div class="event-detail-tickets event-detail-tickets-layout">
                <?php if (!$hasAnyTicketOption): ?>
                    <p class="copy-text event-detail-tickets-empty">Tickets for this event are not available online yet. Browse
                        all dance tickets or check back soon.</p>
                    <div class="event-detail-tickets-cta event-detail-tickets-cta--figma">
                        <a href="/tickets?cat=dance" class="btn btn--outline">Browse dance tickets</a>
                    </div>
                <?php else: ?>
                    <div class="event-detail-tickets-grid event-detail-tickets-grid--figma">
                        <?php if ($hasEventTickets): ?>
                            <div class="<?= $standardCellClass ?>">
                                <?php
                                $eventCount = count($vm->eventTickets);
                                foreach ($vm->eventTickets as $ticket):
                                    $tdId = $intVal($ticket, 'ticket_details_id');
                                    $tName = $text($ticket, 'name');
                                    if ($tName === '') {
                                        $tName = 'Ticket';
                                    }
                                    $tDesc = $text($ticket, 'description');
                                    $isFree = !empty($ticket['is_free']);
                                    $isVip = stripos($tName, 'VIP') !== false;
                                    $stock = $stockOf($ticket);
                                    $remaining = $intVal($stock, 'remaining');
                                    $soldOut = !empty($stock['sold_out']);
                                    $priceRaw = 0;
                                    if (isset($ticket['price'])) {
                                        $priceRaw = $ticket['price'];
                                    }
                                    $price = $formatTicketEur($priceRaw, $isFree);

                                    // a single non-VIP ticket reads better as "Standard Ticket"
                                    $cardTitle = $tName;
                                    if ($eventCount === 1 && !$isVip) {
                                        $cardTitle = 'Standard Ticket';
                                    }
                                    $cardClass = 'event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--flex';
                                    if ($isVip) {
                                        $cardClass .= ' vip';
                                    }
                                    $canBuy = $tdId > 0 && !$soldOut;
                                    ?>
                                    <article class="<?= $cardClass ?>">
                                        <?php if ($isVip): ?>
                                            <span class="event-detail-ticket-badge event-detail-ticket-badge--vip-figma">VIP</span>
                                        <?php endif; ?>
                                        <div class="event-detail-ticket-card-head">
                                            <h3 class="event-detail-ticket-title"><?= $h($cardTitle) ?></h3>
                                            <p class="event-detail-ticket-price"><?= $h($price) ?></p>
                                        </div>
                                        <?php if ($soldOut): ?>
                                            <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold
                                                out</p>
                                        <?php elseif (!empty($stock['nearly'])): ?>
                                            <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost
                                                sold out</p>
                                        <?php elseif (!empty($stock['low_stock'])): ?>
                                            <p class="event-detail-stock-badge event-detail-stock-badge--low">
                                                Only <?= $h((string) $remaining) ?> left</p>
                                        <?php endif; ?>
                                        <?php if ($tDesc !== ''): ?>
                                            <?php $renderFeatureList($tDesc); ?>
                                        <?php else: ?>
                                            <p class="event-detail-ticket-features event-detail-ticket-features--figma">
                                                Access to this event</p>
                                        <?php endif; ?>
                                        <?php if ($canBuy): ?>
                                            <!-- add to cart with quantity 1; the cart page handles edits -->
                                            <form method="post" action="/cart/add" class="event-detail-cart-form">
                                                <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                                <input type="hidden" name="ticket_details_id" value="<?= $tdId ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="return" value="<?= $h($cartReturn) ?>">
                                                <button type="submit" class="btn btn--light btn--block">Buy
                                                    tickets
                                                </button>
                                            </form>
                                        <?php elseif ($tdId > 0 && $soldOut): ?>
                                            <p class="event-detail-cart-form"><span
                                                        class="btn btn--light btn--block is-disabled"
                                                        aria-disabled="true">Sold out</span></p>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasDayPass):
                            $pass = $vm->danceDayPass;
                            $tdId = $intVal($pass, 'ticket_details_id');
                            $pName = $text($pass, 'name');
                            if ($pName === '') {
                                $pName = 'Day Pass';
                            }
                            $pDesc = $text($pass, 'description');
                            $isFree = !empty($pass['is_free']);
                            $stock = $stockOf($pass);
                            $remaining = $intVal($stock, 'remaining');
                            $soldOut = !empty($stock['sold_out']);
                            $priceRaw = 0;
                            if (isset($pass['price'])) {
                                $priceRaw = $pass['price'];
                            }
                            $price = $formatTicketEur($priceRaw, $isFree);
                            $passDay = $text($pass, 'pass_day');
                            $passTime = $text($pass, 'pass_time');
                            $canBuy = $tdId > 0 && !$soldOut && !$isFree;
                            ?>
                            <div class="<?= $dayCellClass ?>">
                                <article
                                        class="event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--pass event-detail-ticket-card--flex">
                                    <div class="event-detail-ticket-card-head">
                                        <h3 class="event-detail-ticket-title"><?= $h($pName) ?></h3>
                                        <p class="event-detail-ticket-price"><?= $h($price) ?></p>
                                    </div>
                                    <?php if ($soldOut): ?>
                                        <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold
                                            out</p>
                                    <?php elseif (!empty($stock['nearly'])): ?>
                                        <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost sold
                                            out</p>
                                    <?php elseif (!empty($stock['low_stock'])): ?>
                                        <p class="event-detail-stock-badge event-detail-stock-badge--low">
                                            Only <?= $h((string) $remaining) ?> left</p>
                                    <?php endif; ?>
                                    <?php if ($pDesc !== ''): ?>
                                        <?php $renderFeatureList($pDesc); ?>
                                    <?php endif; ?>
                                    <?php if ($passDay !== '' || $passTime !== ''): ?>
                                        <!-- optional day-pass schedule, shown only when set -->
                                        <p class="event-detail-pass-meta event-detail-pass-meta--figma">
                                            <?php if ($passDay !== ''): ?>
                                                <span><?= $h(ucfirst($passDay)) ?> pass</span>
                                            <?php endif; ?>
                                            <?php if ($passTime !== ''): ?>
                                                <span class="event-detail-pass-meta-sep"> · </span>
                                                <span><?= $h($passTime) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($canBuy): ?>
                                        <form method="post" action="/cart/add" class="event-detail-cart-form">
                                            <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                            <input type="hidden" name="ticket_details_id" value="<?= $tdId ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="return" value="<?= $h($cartReturn) ?>">
                                            <button type="submit" class="btn btn--light">Buy tickets
                                            </button>
                                        </form>
                                    <?php elseif ($tdId > 0 && $soldOut): ?>
                                        <p class="event-detail-cart-form"><span
                                                    class="btn btn--light btn--block is-disabled"
                                                    aria-disabled="true">Sold out</span></p>
                                    <?php endif; ?>
                                </article>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasFestivalPass):
                            $pass = $vm->danceAllAccessPass;
                            $tdId = $intVal($pass, 'ticket_details_id');
                            $pName = $text($pass, 'name');
                            if ($pName === '') {
                                $pName = 'All-Access Pass';
                            }
                            $pDesc = $text($pass, 'description');
                            $isFree = !empty($pass['is_free']);
                            $stock = $stockOf($pass);
                            $remaining = $intVal($stock, 'remaining');
                            $soldOut = !empty($stock['sold_out']);
                            $priceRaw = 0;
                            if (isset($pass['price'])) {
                                $priceRaw = $pass['price'];
                            }
                            $price = $formatTicketEur($priceRaw, $isFree);
                            $scheduleDisplay = $text($pass, 'schedule_display');
                            $canBuy = $tdId > 0 && !$soldOut && !$isFree;
                            ?>
                            <div class="event-detail-tickets-cell event-detail-tickets-cell--full">
                                <article
                                        class="event-detail-ticket-card event-detail-ticket-card--figma event-detail-ticket-card--festival event-detail-ticket-card--flex">
                                    <span class="event-detail-ticket-badge event-detail-ticket-badge--best">BEST VALUE</span>
                                    <div class="event-detail-ticket-card-head">
                                        <h3 class="event-detail-ticket-title"><?= $h($pName) ?></h3>
                                        <p class="event-detail-ticket-price"><?= $h($price) ?></p>
                                    </div>
                                    <?php if ($soldOut): ?>
                                        <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold
                                            out</p>
                                    <?php elseif (!empty($stock['nearly'])): ?>
                                        <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost sold
                                            out</p>
                                    <?php elseif (!empty($stock['low_stock'])): ?>
                                        <p class="event-detail-stock-badge event-detail-stock-badge--low">
                                            Only <?= $h((string) $remaining) ?> left</p>
                                    <?php endif; ?>
                                    <?php if ($pDesc !== ''): ?>
                                        <?php $renderFeatureListTwoCol($pDesc); ?>
                                    <?php endif; ?>
                                    <?php if ($scheduleDisplay !== ''): ?>
                                        <!-- schedule string set in the CMS for the all-access pass -->
                                        <p class="event-detail-pass-meta event-detail-pass-meta--figma"><?= $h($scheduleDisplay) ?></p>
                                    <?php endif; ?>
                                    <?php if ($canBuy): ?>
                                        <form method="post" action="/cart/add" class="event-detail-cart-form">
                                            <input type="hidden" name="_csrf" value="<?= $h($cartFormCsrf) ?>">
                                            <input type="hidden" name="ticket_details_id" value="<?= $tdId ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="return" value="<?= $h($cartReturn) ?>">
                                            <button type="submit" class="btn btn--light">Buy tickets
                                            </button>
                                        </form>
                                    <?php elseif ($tdId > 0 && $soldOut): ?>
                                        <p class="event-detail-cart-form"><span
                                                    class="btn btn--light btn--block is-disabled"
                                                    aria-disabled="true">Sold out</span></p>
                                    <?php endif; ?>
                                </article>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="event-detail-tickets-cta event-detail-tickets-cta--figma">
                        <a href="/cart" class="btn btn--light">Book now</a>
                        <a href="/tickets?cat=dance" class="btn btn--outline">All dance tickets</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="event-detail-group event-detail-group--panel event-detail-group--location">
        <section class="event-detail-location event-detail-location--grouped" aria-labelledby="location-heading">
            <div class="event-detail-location-inner">
                <p class="event-detail-group-label">Location</p>
                <h2 class="section-title section-title--accent event-detail-group-title event-detail-location__title"
                    id="location-heading"><?= htmlspecialchars($vm->locationDisplay) ?></h2>
                <p class="copy-text copy-text--sm event-detail-location-hint">Easy to reach by public transport or by car</p>
                <div class="event-detail-map">
                    <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $vm->mapLon - 0.02 ?>%2C<?= $vm->mapLat - 0.015 ?>%2C<?= $vm->mapLon + 0.02 ?>%2C<?= $vm->mapLat + 0.015 ?>&layer=mapnik&marker=<?= $vm->mapLat ?>%2C<?= $vm->mapLon ?>"
                            width="100%" height="100%" loading="lazy"
                            title="Map of <?= htmlspecialchars($event->venueName) ?>"></iframe>
                </div>
                <a href="https://www.google.com/maps/search/?api=1&query=<?= htmlspecialchars($vm->mapQuery) ?>"
                   target="_blank" rel="noopener" class="btn btn--light">Open in Google Maps</a>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
