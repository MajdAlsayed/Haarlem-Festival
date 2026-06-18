<?php

$event = $vm->event;
$appSettings = $vm->appSettings;
$breadcrumbs = $vm->breadcrumbs;
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = $vm->pageTitle;
$pageStyles = ['/css/pages/dance.css'];
$bodyClass = 'dance-page event-detail-page';

// hero values read by the page-hero partial
$pageHeroTitle = $vm->pageHeroTitle;
$pageHeroSubtitle = $vm->eventSubtitle;
$pageHeroImage = $vm->heroImage;
$pageHeroAlt = $event->title;
$pageHeroClass = 'dance-detail-hero dance-event-detail-hero';
$pageHeroContentClass = 'dance-detail-hero__content dance-event-detail-hero__content';

$hasTickets = $vm->eventTicketCards !== [] || $vm->dayPassCard !== null || $vm->festivalPassCard !== null;

// one cart toast: success first, otherwise an error
$cartToastMsg = '';
$cartToastClass = 'event-detail-cart-toast';
$cartToastIcon = '✓';
if (!empty($vm->cartFlashSuccess)) {
    $cartToastMsg = (string) $vm->cartFlashSuccess;
} elseif (!empty($vm->cartFlashError)) {
    $cartToastMsg = (string) $vm->cartFlashError;
    $cartToastClass = 'event-detail-cart-toast event-detail-cart-toast--error';
    $cartToastIcon = '!';
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

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
                                        class="value"><?= htmlspecialchars($vm->dateTimeLine) ?></span></div>
                        </li>
                        <li>
                            <div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon"
                                                        aria-hidden="true"></div>
                            <div class="event-detail-info-item"><span class="label">Location</span><span
                                        class="value"><?= htmlspecialchars($vm->venueLine) ?></span>
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
            <h2 class="event-detail-tickets-figma-title" id="tickets-heading"><?= $h($vm->ticketsFigmaTitle) ?></h2>
            <div class="event-detail-tickets event-detail-tickets-layout">
                <?php if (!$hasTickets): ?>
                    <p class="copy-text event-detail-tickets-empty">Tickets for this event are not available online yet. Browse
                        all dance tickets or check back soon.</p>
                    <div class="event-detail-tickets-cta event-detail-tickets-cta--figma">
                        <a href="/tickets?cat=dance" class="btn btn--outline">Browse dance tickets</a>
                    </div>
                <?php else: ?>
                    <div class="event-detail-tickets-grid event-detail-tickets-grid--figma">
                        <?php if ($vm->eventTicketCards !== []): ?>
                            <div class="<?= $vm->standardCellClass ?>">
                                <?php foreach ($vm->eventTicketCards as $card): ?>
                                    <?php require __DIR__ . '/partials/event-ticket-card.php'; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($vm->dayPassCard !== null): ?>
                            <div class="<?= $vm->dayCellClass ?>">
                                <?php $card = $vm->dayPassCard; require __DIR__ . '/partials/event-ticket-card.php'; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($vm->festivalPassCard !== null): ?>
                            <div class="event-detail-tickets-cell event-detail-tickets-cell--full">
                                <?php $card = $vm->festivalPassCard; require __DIR__ . '/partials/event-ticket-card.php'; ?>
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
