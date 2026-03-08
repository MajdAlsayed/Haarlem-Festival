<?php
$e = $viewModel->event;
$app = (new \App\Repositories\SettingsRepository())->getAll();
$photosRepo = new \App\Repositories\PhotosRepository();
$isArminClubSession = (stripos($e->title ?? '', 'Armin') !== false && stripos($e->title ?? '', 'Trance Club') !== false && ($e->venueName ?? '') === 'Jopenkerk');
$isHardwellClubNight = (stripos($e->title ?? '', 'Hardwell') !== false && ($e->venueName ?? '') === 'XO the Club');
$isB2B2B = (stripos($e->title ?? '', 'B2B2B') !== false && ($e->venueName ?? '') === 'Caprera Openluchttheater');
$heroKey = $isArminClubSession ? 'hero_club' : ($isHardwellClubNight ? 'hero_clubnight' : 'hero_default');
$heroFilename = $photosRepo->getFilename('dance_event_detail', $heroKey) ?? ($isArminClubSession ? 'DetailsPage/clubhero.png' : ($isHardwellClubNight ? 'DetailsPage/clubnighthero.png' : 'DetailsPage/hero.png'));
$heroImage = '/images/dance/' . $heroFilename;
$eventGalleryPrefix = $isArminClubSession ? 'gallery_club' : ($isHardwellClubNight ? 'gallery_clubnight' : 'gallery_default');
$eventGallery1 = $photosRepo->getFilename('dance_event_detail', $eventGalleryPrefix . '_1') ?? ($isArminClubSession ? 'DetailsPage/club1.png' : ($isHardwellClubNight ? 'DetailsPage/clubnight1.png' : 'DetailsPage/2.png'));
$eventGallery2 = $photosRepo->getFilename('dance_event_detail', $eventGalleryPrefix . '_2') ?? ($isArminClubSession ? 'DetailsPage/club2.png' : ($isHardwellClubNight ? 'DetailsPage/clubnight2.png' : 'DetailsPage/3.png'));
$eventGallery3 = $photosRepo->getFilename('dance_event_detail', $eventGalleryPrefix . '_3') ?? ($isArminClubSession ? 'DetailsPage/club3.png' : ($isHardwellClubNight ? 'DetailsPage/clubnight3.png' : 'DetailsPage/4.png'));
$fullAddress = trim(($e->venueAddress ?? '') . ', ' . ($e->venueCity ?? ''));
$mapQuery = urlencode($e->venueName . ' ' . $fullAddress);
$locationDisplay = $e->venueName . ' — Haarlem, Netherlands';
$venueCoords = [
    'Caprera Openluchttheater' => [52.4112, 4.6062],
    'Jopenkerk' => [52.3813, 4.6368],
    'Lichtfabriek' => [52.3890, 4.6330],
    'Patronaat' => [52.3820, 4.6380],
    'XO the Club' => [52.3815, 4.6370],
];
$coord = $venueCoords[$e->venueName] ?? [52.3813, 4.6368];
$mapLat = $coord[0];
$mapLon = $coord[1];
$formattedDate = $viewModel->getFormattedDate();
$startTime = $e->startTime ?? '14:00';
$dateTimeLine = $formattedDate ? ($formattedDate . ' • ' . $startTime) : $startTime;
$artistsDisplay = preg_match('/^(.+?)\s*[–—-]\s*.+$/', $e->title, $am) ? str_replace([' / ', '/'], [', ', ', '], trim($am[1])) : $e->title;
$eventSubtitle = $isArminClubSession ? 'Club Session' : ($isHardwellClubNight ? 'Exclusive Club Night' : (preg_match('/^.+?[–—-]\s*(.+)$/', $e->title, $sm) ? trim($sm[1]) : $e->title));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>
<body class="dance-page event-detail-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="event-detail-hero" style="background-image: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 40%, rgba(0,0,0,0.6) 100%), url('<?= htmlspecialchars($heroImage) ?>');">
        <div class="event-detail-hero-overlay"></div>
        <div class="event-detail-hero-content">
            <?php if ($isArminClubSession): ?>
            <p class="event-detail-hero-venue"><?= htmlspecialchars('Armin van Buuren') ?></p>
            <h1 class="event-detail-hero-title">
                <span class="title-orange"><?= htmlspecialchars($eventSubtitle) ?></span>
            </h1>
            <?php elseif ($isHardwellClubNight): ?>
            <p class="event-detail-hero-venue"><?= htmlspecialchars('Hardwell') ?></p>
            <h1 class="event-detail-hero-title">
                <span class="title-orange"><?= htmlspecialchars($eventSubtitle) ?></span>
            </h1>
            <?php else: ?>
            <p class="event-detail-hero-venue"><?= htmlspecialchars($e->venueName) ?></p>
            <h1 class="event-detail-hero-title">
                <span class="title-orange"><?= htmlspecialchars($eventSubtitle) ?></span>
            </h1>
            <?php endif; ?>
            <a href="#event-info" class="event-detail-hero-btn">More info <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>

    <nav class="breadcrumbs dance-breadcrumbs event-detail-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">HOME</a>
        <span class="breadcrumb-sep">›</span>
        <a href="/dance">DANCE</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current"><?= htmlspecialchars($isArminClubSession ? 'CLUB SESSION' : ($isHardwellClubNight ? 'EXCLUSIVE CLUB NIGHT' : strtoupper($e->venueName))) ?></span>
    </nav>

    <section id="event-info" class="event-detail-section">
        <div class="event-detail-overview">
            <div class="event-detail-overview-image">
                <img src="/images/dance/<?= htmlspecialchars($eventGallery1) ?>" alt="<?= htmlspecialchars($e->title) ?>" onerror="this.parentElement.style.display='none'">
            </div>
            <div class="event-detail-overview-info">
                <h2>Event Information</h2>
                <ul class="event-detail-info-list">
                    <?php if ($isHardwellClubNight): ?>
                    <li><div class="icon-wrap"><img src="/images/icons/dateIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Date &amp; Time</span><span class="value">Sunday • 21:00</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Location</span><span class="value">XO the Club, Haarlem</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/musicIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Genre</span><span class="value">Dance</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/artistIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Artist(s)</span><span class="value">Hardwell</span></div></li>
                    <?php elseif ($isArminClubSession): ?>
                    <li><div class="icon-wrap"><img src="/images/icons/dateIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Date &amp; Time</span><span class="value">Sunday • 19:00</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Location</span><span class="value">Jopenkerk, Haarlem</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/musicIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Genre</span><span class="value">Trance &amp; Techno</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/artistIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Artist(s)</span><span class="value">Armin van Buuren</span></div></li>
                    <?php else: ?>
                    <li><div class="icon-wrap"><img src="/images/icons/dateIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Date &amp; Time</span><span class="value"><?= htmlspecialchars($dateTimeLine) ?></span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/locationIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Location</span><span class="value"><?= htmlspecialchars($e->venueName . ($e->venueCity ? ', ' . $e->venueCity : '')) ?></span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/musicIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Genre</span><span class="value">Mixed Genres</span></div></li>
                    <li><div class="icon-wrap"><img src="/images/icons/artistIcon.png" alt="" class="icon" aria-hidden="true"></div><div class="event-detail-info-item"><span class="label">Artist(s)</span><span class="value"><?= htmlspecialchars($artistsDisplay) ?></span></div></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="event-detail-section event-detail-section-alt">
        <div class="event-detail-features">
            <div class="event-detail-desc-text">
                <h2>Description</h2>
                <div class="event-detail-desc-paragraphs"><?= $isHardwellClubNight ? 'Hardwell closes the festival with a high-energy, dance-heavy finale at XO the Club. Expect Hardwell\'s explosive drops, signature big-room sound, and an unforgettable final-night atmosphere inside one of Haarlem\'s most vibrant nightlife venues.' : ($isArminClubSession ? 'Armin van Buuren brings his signature trance and techno sound to the iconic Jopenkerk in Haarlem. This exclusive club session delivers an intense, high-energy experience in an intimate indoor venue, combining deep melodies, driving beats, and immersive sound.' : nl2br(htmlspecialchars($e->description ?? ''))) ?></div>
                <h3 class="event-detail-features-heading"><?= $isHardwellClubNight ? 'This showcase features:' : ($isArminClubSession ? 'This showcase features:' : 'This one-night show features') ?></h3>
                <ul>
                    <?php if ($isHardwellClubNight): ?>
                    <li><img src="/images/icons/musicIcon.png" alt="" class="feat-icon" aria-hidden="true"> High-energy dance sound &amp; club beats</li>
                    <li><img src="/images/icons/SoundIcon.png" alt="" class="feat-icon" aria-hidden="true"> Hardwell's festival-style drops and transitions</li>
                    <li><img src="/images/icons/lightIcon.png" alt="" class="feat-icon" aria-hidden="true"> Full LED &amp; laser light setup</li>
                    <li><img src="/images/icons/eyeIcon.png" alt="" class="feat-icon" aria-hidden="true"> Atmospheric smoke &amp; club visuals</li>
                    <li><img src="/images/icons/webIcon.png" alt="" class="feat-icon" aria-hidden="true"> Up-close club experience with an intimate crowd</li>
                    <?php elseif ($isArminClubSession): ?>
                    <li><img src="/images/icons/musicIcon.png" alt="" class="feat-icon" aria-hidden="true"> Powerful trance anthems</li>
                    <li><img src="/images/icons/SoundIcon.png" alt="" class="feat-icon" aria-hidden="true"> Driving techno influences</li>
                    <li><img src="/images/icons/lightIcon.png" alt="" class="feat-icon" aria-hidden="true"> Extended club-style DJ set</li>
                    <li><img src="/images/icons/eyeIcon.png" alt="" class="feat-icon" aria-hidden="true"> Immersive lighting and sound</li>
                    <li><img src="/images/icons/webIcon.png" alt="" class="feat-icon" aria-hidden="true"> Intimate crowd experience</li>
                    <?php else: ?>
                    <li><img src="/images/icons/musicIcon.png" alt="" class="feat-icon" aria-hidden="true"> World-class electronic DJs</li>
                    <li><img src="/images/icons/SoundIcon.png" alt="" class="feat-icon" aria-hidden="true"> Heavy techno sound</li>
                    <li><img src="/images/icons/lightIcon.png" alt="" class="feat-icon" aria-hidden="true"> Synchronized laser and light shows</li>
                    <li><img src="/images/icons/eyeIcon.png" alt="" class="feat-icon" aria-hidden="true"> Industrial-style visual stage design</li>
                    <li><img src="/images/icons/webIcon.png" alt="" class="feat-icon" aria-hidden="true"> Massive outdoor space for thousands of visitors</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="event-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($eventGallery2) ?>" alt="<?= $isArminClubSession ? 'Club session' : ($isHardwellClubNight ? 'Hardwell club night' : 'Caprera stage') ?>" onerror="this.style.display='none'">
            </div>
        </div>
    </section>

    <section class="event-detail-section">
        <div class="event-detail-features">
            <div class="event-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($eventGallery3) ?>" alt="<?= $isArminClubSession ? 'What to expect' : ($isHardwellClubNight ? 'What to expect' : 'Event') ?>" onerror="this.style.display='none'">
            </div>
            <div class="event-detail-desc-text">
                <h2>What to Expect</h2>
                <ul class="event-detail-expect-list">
                    <?php if ($isHardwellClubNight): ?>
                    <li>Signature Hardwell Sound</li>
                    <li>High-energy club atmosphere with intense crowd connection</li>
                    <li>A 90-minute set built for peak-time club energy</li>
                    <li>A powerful DJ performance featuring Hardwell's modern dance and house sound</li>
                    <?php elseif ($isArminClubSession): ?>
                    <li>Signature Armin van Buuren Sound</li>
                    <li>Historic indoor venue with close crowd connection and focused sound design.</li>
                    <li>A 90-minute set designed for peak-time club intensity.</li>
                    <li>A full DJ set showcasing Armin's modern and classic festival sound.</li>
                    <?php else: ?>
                    <li>Three world-class DJs, back-to-back</li>
                    <li>Huge outdoor festival stage</li>
                    <li>High-energy EDM &amp; trance combinations</li>
                    <li>Immersive visuals and laser shows</li>
                    <li>Family-friendly outdoor setup</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </section>

    <section id="tickets" class="event-detail-section event-detail-section-alt">
        <h2>Tickets</h2>
        <div class="event-detail-tickets">
            <?php if ($isArminClubSession): ?>
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">Standard Ticket</h3>
                <p class="event-detail-ticket-price">€60,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to Armin van Buuren live</li>
                    <li>Club session (90 minutes)</li>
                    <li>Indoor venue: Jopenkerk</li>
                    <li>Standing audience</li>
                    <li>Limited availability</li>
                </ul>
            </article>
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">All-Access Day Pass</h3>
                <p class="event-detail-ticket-price">€150,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to all DANCE! events on Sunday</li>
                    <li>Includes Armin van Buuren @ Jopenkerk</li>
                    <li>Ideal when attending multiple Sunday events</li>
                </ul>
            </article>
            <article class="event-detail-ticket-card event-detail-ticket-card-full vip">
                <span class="event-detail-ticket-badge">BEST VALUE</span>
                <h3 class="event-detail-ticket-title">All-Access Festival Pass</h3>
                <p class="event-detail-ticket-price">€250,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to all DANCE! events on Friday, Saturday &amp; Sunday</li>
                    <li>Includes: Armin van Buuren @ Jopenkerk, Hardwell @ XO the Club, Caprera Openluchttheater session</li>
                    <li>Full festival access</li>
                </ul>
            </article>
            <div class="event-detail-tickets-cta">
                <a href="#" class="btn btn-primary btn-white">BOOK NOW <span aria-hidden="true">&#8594;</span></a>
            </div>
            <?php elseif ($isB2B2B): ?>
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">Standard Ticket</h3>
                <p class="event-detail-ticket-price">€110,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to Back2Back outdoor session</li>
                    <li>Artists: Hardwell, Martin Garrix, Armin van Buuren</li>
                    <li>Long session (approx. 540 minutes)</li>
                    <li>Open-air venue experience</li>
                    <li>Limited capacity</li>
                </ul>
            </article>
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">All-Access Day Pass</h3>
                <p class="event-detail-ticket-price">€150,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to all DANCE! events on Saturday</li>
                    <li>Includes Caprera Openluchttheater session</li>
                    <li>Best option for multiple Saturday events</li>
                </ul>
            </article>
            <article class="event-detail-ticket-card event-detail-ticket-card-full vip">
                <span class="event-detail-ticket-badge">BEST VALUE</span>
                <h3 class="event-detail-ticket-title">All-Access Festival Pass</h3>
                <p class="event-detail-ticket-price">€250,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to all DANCE! events on Friday, Saturday &amp; Sunday</li>
                    <li>One pass for the full festival weekend</li>
                    <li>Includes this Caprera session</li>
                </ul>
            </article>
            <div class="event-detail-tickets-cta">
                <a href="#" class="btn btn-primary btn-white">BOOK NOW <span aria-hidden="true">&#8594;</span></a>
            </div>
            <?php elseif ($isHardwellClubNight): ?>
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">Standard</h3>
                <p class="event-detail-ticket-price">€90,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to Hardwell live performance</li>
                    <li>Entry to XO the Club</li>
                    <li>Club session (90 minutes)</li>
                    <li>Standing audience</li>
                    <li>Limited availability</li>
                </ul>
            </article>
            <article class="event-detail-ticket-card">
                <h3 class="event-detail-ticket-title">All-Access Day Pass</h3>
                <p class="event-detail-ticket-price">€150,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to all DANCE! events on the same day</li>
                    <li>Includes Hardwell @ XO the Club</li>
                    <li>Priority access where available</li>
                    <li>Best option if attending multiple events that day</li>
                </ul>
            </article>
            <article class="event-detail-ticket-card event-detail-ticket-card-full vip">
                <span class="event-detail-ticket-badge">BEST VALUE</span>
                <h3 class="event-detail-ticket-title">All-Access Day Pass</h3>
                <p class="event-detail-ticket-price">€250,00</p>
                <ul class="event-detail-ticket-features-list">
                    <li>Access to all DANCE! events on Friday, Saturday &amp; Sunday</li>
                    <li>Hardwell @ XO the Club (Sunday)</li>
                    <li>Armin van Buuren (Sunday)</li>
                    <li>Caprera Openluchttheater session (Saturday)</li>
                    <li>One pass for the entire weekend</li>
                </ul>
            </article>
            <div class="event-detail-tickets-cta">
                <a href="#" class="btn btn-primary btn-white">BOOK NOW <span aria-hidden="true">&#8594;</span></a>
            </div>
            <?php else: ?>
            <article class="event-detail-ticket-card">
                <p class="event-detail-ticket-price">€ 112,00</p>
                <p class="event-detail-ticket-features">Access to event<br>Free parking<br>Event program</p>
                <a href="#" class="btn btn-primary btn-white">Buy tickets</a>
            </article>
            <article class="event-detail-ticket-card vip">
                <span class="event-detail-ticket-badge">VIP PASS</span>
                <p class="event-detail-ticket-price">€ 250,00</p>
                <p class="event-detail-ticket-features">Access to ALL 3 Caprera Outdoor Events<br>Private VIP entrance<br>Complimentary drink and food</p>
                <a href="#" class="btn btn-primary btn-white">Buy tickets</a>
            </article>
            <article class="event-detail-ticket-card">
                <p class="event-detail-ticket-price">€ 350,00</p>
                <p class="event-detail-ticket-features">Exclusive box seating<br>Private bar and waiter service<br>Backstage access</p>
                <a href="#" class="btn btn-primary btn-white">Buy tickets</a>
            </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="event-detail-location" aria-labelledby="location-heading">
        <div class="event-detail-location-inner">
            <h2 id="location-heading"><?= htmlspecialchars($locationDisplay) ?></h2>
            <p class="event-detail-location-hint">Easy to reach by public transport or by car</p>
            <div class="event-detail-map">
                <iframe src="https://www.openstreetmap.org/export/embed.html?bbox=<?= $mapLon - 0.02 ?>%2C<?= $mapLat - 0.015 ?>%2C<?= $mapLon + 0.02 ?>%2C<?= $mapLat + 0.015 ?>&layer=mapnik&marker=<?= $mapLat ?>%2C<?= $mapLon ?>" width="100%" height="100%" loading="lazy" title="Map of <?= htmlspecialchars($e->venueName) ?>"></iframe>
            </div>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= htmlspecialchars($mapQuery) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-white">Open in Google Maps</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
