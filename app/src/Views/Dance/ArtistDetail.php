<?php
/** @var array{id: int, name: string, slug: string|null, bio: string|null, image: string} $artist */
/** @var \App\Models\Event[] $artistEvents */
/** @var list<string> $galleryImages */
$app = (new \App\Repositories\SettingsRepository())->getAll();
$photosRepo = new \App\Repositories\PhotosRepository();
$artistBase = '/images/dance/';
$heroSlug = strtolower($artist['slug'] ?? '');
$scheduleImage = $photosRepo->getFilename('dance_artist_schedule', $heroSlug) ?? ($heroSlug === 'tiesto' ? 'Artist/tiesto3.png' : ($photosRepo->getFilename('dance_artist_schedule', null) ?? 'Artist/hardwell6.png'));
$heroFilename = $photosRepo->getFilename('dance_artist_hero', $heroSlug) ?? ($heroSlug === 'tiesto' ? 'Artist/tiestohero.png' : $artist['image']);
$heroImage = $artistBase . $heroFilename;
$dayLabels = ['friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
$hardwellAbout = [
    'Hardwell is one of the Netherlands\' biggest electronic music names, known for his energetic mainstage sound and powerful club performances.',
    'His sets combine festival-level intensity with sharp, modern dance drops making him a guaranteed crowd favorite at Haarlem Dance 2026.',
    'Whether he\'s playing exclusive shows or closing major events, Hardwell always brings a fast, dynamic, and high-energy experience.',
];
$hardwellHighlights = [
    'Headliner at major festivals including Tomorrowland and Ultra',
    'Voted #1 DJ in the World twice by DJ Mag',
    'Founder of Revealed Recordings',
    'Produced global hits like Spaceman, Apollo, and Zero 76',
    'Known for high-energy big-room sets and innovative live performances',
];
$tiestoAbout = [
    'Tiësto is one of the most influential electronic artists in the world. Known for his signature blend of trance, electro, and festival-ready sounds, he continues to innovate with every performance.',
    'His emotional melodies, powerful drops, and decades of experience make him a global dance icon.',
];
$tiestoHighlights = [
    'Grammy Award-winning DJ & producer',
    'Performed at the Olympics Opening Ceremony (Athens 2004)',
    'Known for legendary albums like Just Be and Elements of Life',
    'Pioneer of trance, then global leader in EDM and electro',
    'Millions of monthly listeners across decades of music',
];
$isHardwell = $heroSlug === 'hardwell';
$isTiesto = $heroSlug === 'tiesto';
$hasFullPage = $isHardwell || $isTiesto;
$aboutParagraphs = $isHardwell ? $hardwellAbout : ($isTiesto ? $tiestoAbout : null);
$careerHighlights = $isHardwell ? $hardwellHighlights : ($isTiesto ? $tiestoHighlights : null);
$heroDisplayName = $isHardwell ? 'Robbert Hardwell' : ($isTiesto ? 'Tiësto' : $artist['name']);
$heroTagline = $isHardwell ? 'High-energy dance' : ($isTiesto ? 'Trance and electro' : ($artist['bio'] ? substr($artist['bio'], 0, 50) . '...' : ''));
$careerImage = $galleryImages[0] ?? $artist['image'];

// Music section: per-artist data (set for Tiësto; Hardwell uses partial defaults)
if ($isTiesto) {
    $musicDisplayName = 'TIËSTO';
    $musicRealName = 'Tiësto';
    $musicLocation = 'The World is My Home';
    $musicAlbumTitle = 'Tiësto';
    $musicAlbumSub = 'Featured';
    $musicTracks = [
        ['title' => 'RVN (Raven)', 'duration' => '3:24', 'audio' => '/audio/' . rawurlencode('Tiësto - RVN (Raven).mp3')],
        ['title' => 'Drifting (Arodes Remix)', 'duration' => '4:12', 'audio' => '/audio/' . rawurlencode('Tiësto - Drifting (Official Music Video).mp3')],
        ['title' => 'Everlight', 'duration' => '5:24', 'audio' => '/audio/' . rawurlencode('Tiësto Mathame - Everlight (Official Audio).mp3')],
    ];
    $musicExtraTracks = [];
}
// Gallery stats: per-artist (Hardwell 250/500/100/80, Tiësto 300/700/150/60)
$galleryStats = $isTiesto
    ? [['num' => '300+', 'label' => 'PHOTOS'], ['num' => '700+', 'label' => 'LIVE SHOWS'], ['num' => '150+', 'label' => 'FESTIVALS'], ['num' => '60+', 'label' => 'COUNTRIES']]
    : [['num' => '250+', 'label' => 'PHOTOS'], ['num' => '500+', 'label' => 'LIVE SHOWS'], ['num' => '100+', 'label' => 'FESTIVALS'], ['num' => '80+', 'label' => 'COUNTRIES']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artist['name']) ?> — Haarlem Festival</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>
<body class="dance-page artist-detail-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="artist-detail-hero<?= $isHardwell ? ' artist-detail-hero-hardwell' : ($isTiesto ? ' artist-detail-hero-tiesto' : '') ?>" style="background-image: linear-gradient(to right, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.15) 45%, transparent 70%), linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.25) 50%, rgba(0,0,0,0.65) 100%), url('<?= htmlspecialchars($heroImage) ?>');">
        <div class="artist-detail-hero-content">
            <h1 class="artist-detail-hero-name"><?= htmlspecialchars($heroDisplayName) ?></h1>
            <p class="artist-detail-hero-tagline"><?= htmlspecialchars($heroTagline) ?></p>
            <a href="#about" class="artist-detail-hero-btn">More info <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>

    <nav class="artist-detail-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">HOME</a>
        <span class="breadcrumb-sep">›</span>
        <a href="/dance">DANCE</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current"><?= htmlspecialchars(strtoupper($artist['name'])) ?></span>
    </nav>

    <section id="about" class="artist-detail-section container">
        <h2 class="artist-detail-section-title">About <?= htmlspecialchars($artist['name']) ?></h2>
        <div class="artist-detail-about">
            <div class="artist-detail-about-text">
                <?php if ($aboutParagraphs !== null): ?>
                    <?php foreach ($aboutParagraphs as $p): ?>
                    <p><?= htmlspecialchars($p) ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($artist['bio'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($hasFullPage && $careerHighlights !== null): ?>
    <section class="artist-detail-section artist-detail-section-alt container">
        <div class="artist-detail-features">
            <div class="artist-detail-desc-text">
                <h2 class="artist-detail-section-title">Career Highlights</h2>
                <ul class="artist-detail-highlights">
                    <?php foreach ($careerHighlights as $h): ?>
                    <li><?= htmlspecialchars($h) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="artist-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($careerImage) ?>" alt="<?= htmlspecialchars($artist['name']) ?>" onerror="this.style.display='none'">
            </div>
        </div>
    </section>
    <?php require __DIR__ . '/partials/artist-music-section.php'; ?>
    <?php endif; ?>

    <?php if (!empty($artistEvents)): ?>
    <section class="artist-detail-section artist-detail-schedule-section">
        <h2 class="artist-detail-schedule-title"><span class="artist-detail-schedule-title-main">Festival Schedule</span><span class="artist-detail-schedule-title-sub"> — Haarlem Dance 2026</span></h2>
        <div class="artist-detail-schedule-wrap">
            <div class="artist-detail-schedule-timeline">
                <?php foreach ($artistEvents as $ev): ?>
                <article class="artist-detail-schedule-item">
                    <div class="artist-detail-schedule-dot"></div>
                    <div class="artist-detail-schedule-content">
                        <p class="artist-detail-schedule-time"><span class="artist-detail-schedule-day"><?= htmlspecialchars($dayLabels[$ev->eventDay ?? 'friday'] ?? ucfirst($ev->eventDay ?? '')) ?></span><img src="/images/icons/dateIcon.png" alt="" class="artist-detail-schedule-icon" aria-hidden="true"><span class="artist-detail-schedule-hour"><?= htmlspecialchars($ev->startTime ?? '20:00') ?></span></p>
                        <p class="artist-detail-schedule-venue"><img src="/images/icons/locationIcon.png" alt="" class="artist-detail-schedule-icon" aria-hidden="true"><?= htmlspecialchars($ev->venueName . ', ' . ($ev->venueCity ?? 'Haarlem')) ?><?php if (preg_match('/^.+?[–—-]\s*(.+)$/', $ev->title ?? '', $m) && trim($m[1])): ?> — <span class="artist-detail-schedule-type"><?= htmlspecialchars(trim($m[1])) ?></span><?php endif; ?></p>
                        <p class="artist-detail-schedule-desc"><?= htmlspecialchars($ev->description ?? '') ?></p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="artist-detail-schedule-image">
                <img src="/images/dance/<?= htmlspecialchars($scheduleImage) ?>" alt="<?= htmlspecialchars($artist['name']) ?> performing" onerror="this.style.display='none'">
            </div>
        </div>
        <div class="artist-detail-schedule-cta">
            <a href="/dance/event/<?= (int) ($artistEvents[0]->id ?? 0) ?>" class="artist-detail-schedule-btn">Ticket <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($hasFullPage && count($galleryImages) >= 4): ?>
    <section class="artist-detail-section artist-detail-gallery-section">
        <div class="artist-detail-gallery-header">
            <h2 class="artist-detail-gallery-title">Gallery</h2>
            <span class="artist-detail-gallery-title-line" aria-hidden="true"></span>
            <p class="artist-detail-gallery-sub">Behind the scenes &amp; live moments</p>
        </div>
        <div class="artist-detail-gallery">
            <div class="artist-detail-gallery-item artist-detail-gallery-item-left">
                <img src="/images/dance/<?= htmlspecialchars($galleryImages[0]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> live" onerror="this.style.display='none'">
            </div>
            <div class="artist-detail-gallery-item artist-detail-gallery-item-right">
                <img src="/images/dance/<?= htmlspecialchars($galleryImages[1]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> backstage" onerror="this.style.display='none'">
            </div>
            <div class="artist-detail-gallery-item artist-detail-gallery-item-left">
                <img src="/images/dance/<?= htmlspecialchars($galleryImages[3]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> stage" onerror="this.style.display='none'">
            </div>
            <div class="artist-detail-gallery-item artist-detail-gallery-item-right">
                <img src="/images/dance/<?= htmlspecialchars($galleryImages[2]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> portrait" onerror="this.style.display='none'">
            </div>
        </div>
        <div class="artist-detail-stats">
            <?php foreach ($galleryStats as $stat): ?>
            <div class="artist-detail-stat"><span class="artist-detail-stat-num"><?= htmlspecialchars($stat['num']) ?></span><span class="artist-detail-stat-label"><?= htmlspecialchars($stat['label']) ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="artist-detail-back-wrap">
            <a href="/dance" class="artist-detail-back-btn">BACK <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
