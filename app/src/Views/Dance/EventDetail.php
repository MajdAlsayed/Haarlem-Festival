<?php
/** @var \App\ViewModels\EventDetailViewModel $viewModel */
$event = $viewModel->event;
$appSettings = $viewModel->appSettings;
$dateTimeLine = $viewModel->formattedDate ? ($viewModel->formattedDate . ' • ' . $viewModel->startTime) : $viewModel->startTime;
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

    <section id="event-info" class="event-detail-section">
        <div class="event-detail-overview">
            <?php if (isset($viewModel->galleryImages[0])): ?>
            <div class="event-detail-overview-image">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[0]) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.parentElement.style.display='none'">
            </div>
            <?php endif; ?>
            <div class="event-detail-overview-info">
                <h2>Event Information</h2>
                <ul class="event-detail-info-list">
                    <li><div class="icon-wrap"><img src="/images/icons/dateIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Date &amp; Time</span><span class="value"><?= htmlspecialchars($dateTimeLine) ?></span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Location</span><span class="value"><?= htmlspecialchars($event->venueName . ($event->venueCity ? ', ' . $event->venueCity : '')) ?></span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/musicIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Genre</span><span class="value">Dance</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/artistIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Artist(s)</span><span class="value"><?= htmlspecialchars($viewModel->artistsDisplay) ?></span></div></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="event-detail-section event-detail-section-alt">
        <div class="event-detail-features">
            <div class="event-detail-desc-text">
                <h2>Description</h2>
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

    <section class="event-detail-section">
        <div class="event-detail-features">
            <?php if (isset($viewModel->galleryImages[2])): ?>
            <div class="event-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[2]) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'">
            </div>
            <?php endif; ?>
            <div class="event-detail-desc-text">
                <h2>What to Expect</h2>
                <ul class="event-detail-expect-list">
                    <li>Top electronic and dance artists</li>
                    <li>Premium sound and lighting</li>
                    <li>High-energy atmosphere</li>
                    <li>Unique venue experience</li>
                </ul>
            </div>
        </div>
    </section>

    <section id="tickets" class="event-detail-section event-detail-section-alt">
        <h2>Tickets</h2>
        <div class="event-detail-tickets">
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">Standard Ticket</h3>
                <p class="event-detail-ticket-price">€ 112,00</p>
                <p class="event-detail-ticket-features">Access to event<br>Free parking<br>Event program</p>
                <a href="#" class="btn btn-primary btn-white">Buy tickets</a>
            </article>
            <article class="event-detail-ticket-card vip">
                <span class="event-detail-ticket-badge">VIP PASS</span>
                <p class="event-detail-ticket-price">€ 250,00</p>
                <p class="event-detail-ticket-features">Access to multiple events<br>Private entrance<br>Complimentary drink</p>
                <a href="#" class="btn btn-primary btn-white">Buy tickets</a>
            </article>
            <div class="event-detail-tickets-cta">
                <a href="#" class="btn btn-primary btn-white">BOOK NOW <span aria-hidden="true">&#8594;</span></a>
            </div>
        </div>
    </section>

    <section class="event-detail-location" aria-labelledby="location-heading">
        <div class="event-detail-location-inner">
            <h2 id="location-heading"><?= htmlspecialchars($viewModel->locationDisplay) ?></h2>
            <p class="event-detail-location-hint">Easy to reach by public transport or by car</p>
            <div class="event-detail-map">
                <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $viewModel->mapLon - 0.02 ?>%2C<?= $viewModel->mapLat - 0.015 ?>%2C<?= $viewModel->mapLon + 0.02 ?>%2C<?= $viewModel->mapLat + 0.015 ?>&layer=mapnik&marker=<?= $viewModel->mapLat ?>%2C<?= $viewModel->mapLon ?>" width="100%" height="100%" loading="lazy" title="Map of <?= htmlspecialchars($event->venueName) ?>"></iframe>
            </div>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= htmlspecialchars($viewModel->mapQuery) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-white">Open in Google Maps</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
