<?php
/** Dance artist profile for /dance/artist/{slug}. */
/** @var \App\ViewModels\ArtistDetailViewModel $viewModel */
$artist = $viewModel->artist;
$appSettings = $viewModel->appSettings;
// Map DB day keys to UI labels for schedule rendering.
$dayLabels = ['friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artist['name']) ?> — Haarlem Festival</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($appSettings['css_version'] ?? '1') ?>">
</head>
<body class="dance-page artist-detail-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="artist-detail-hero" style="background-image: linear-gradient(to right, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.15) 45%, transparent 70%), linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.25) 50%, rgba(0,0,0,0.65) 100%), url('<?= htmlspecialchars($viewModel->heroImage) ?>');">
        <div class="artist-detail-hero-content">
            <h1 class="artist-detail-hero-name"><?= htmlspecialchars($artist['name']) ?></h1>
            <?php if (($viewModel->heroTagline ?? '') !== ''): ?>
            <p class="artist-detail-hero-tagline"><?= htmlspecialchars((string) $viewModel->heroTagline) ?></p>
            <?php endif; ?>
            <a href="#about" class="artist-detail-hero-btn">More info <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>

    <nav class="artist-detail-breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($viewModel->breadcrumbs as $i => $crumb): ?>
            <?php if ($i > 0): ?><span class="breadcrumb-sep">›</span><?php endif; ?>
            <?php if (!empty($crumb['url'])): ?>
                <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
            <?php else: ?>
                <span class="breadcrumb-current"><?= htmlspecialchars($crumb['label']) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <section id="about" class="artist-detail-section container">
        <h2 class="artist-detail-section-title">About <?= htmlspecialchars($artist['name']) ?></h2>
        <div class="artist-detail-about">
            <div class="artist-detail-about-text">
                <!-- Prefer curated paragraph splits from the view model; fallback to legacy bio text. -->
                <?php if ($viewModel->aboutParagraphs !== null): ?>
                    <?php foreach ($viewModel->aboutParagraphs as $p): ?>
                    <p><?= htmlspecialchars($p) ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($artist['bio'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($viewModel->careerHighlights !== null && count($viewModel->careerHighlights) > 0): ?>
    <section class="artist-detail-section artist-detail-section-alt container">
        <div class="artist-detail-features">
            <div class="artist-detail-desc-text">
                <h2 class="artist-detail-section-title">Career Highlights</h2>
                <ul class="artist-detail-highlights">
                    <?php foreach ($viewModel->careerHighlights as $h): ?>
                    <li><?= htmlspecialchars($h) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="artist-detail-desc-image">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->careerImage) ?>" alt="<?= htmlspecialchars($artist['name']) ?>" onerror="this.style.display='none'">
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($viewModel->musicTracks) || !empty($viewModel->musicExtraTracks)): ?>
    <!-- Music section is a reusable partial shared by multiple artists. -->
    <?php require __DIR__ . '/partials/artist-music-section.php'; ?>
    <?php endif; ?>

    <?php if (!empty($viewModel->artistEvents)): ?>
    <section class="artist-detail-section artist-detail-schedule-section">
        <h2 class="artist-detail-schedule-title"><span class="artist-detail-schedule-title-main">Festival Schedule</span><span class="artist-detail-schedule-title-sub"> — Haarlem Dance 2026</span></h2>
        <div class="artist-detail-schedule-wrap">
            <div class="artist-detail-schedule-timeline">
                <?php foreach ($viewModel->artistEvents as $ev): ?>
                <article class="artist-detail-schedule-item">
                    <div class="artist-detail-schedule-dot"></div>
                    <div class="artist-detail-schedule-content">
                        <p class="artist-detail-schedule-time"><span class="artist-detail-schedule-day"><?= htmlspecialchars($dayLabels[$ev->eventDay ?? 'friday'] ?? ucfirst($ev->eventDay ?? '')) ?></span><img src="/images/icons/dateIcon.png" alt="" class="artist-detail-schedule-icon" aria-hidden="true"><span class="artist-detail-schedule-hour"><?= htmlspecialchars($ev->startTime ?? '20:00') ?></span></p>
                        <!-- If title contains "Artist - Set Type", extract and render only the set type suffix. -->
                        <p class="artist-detail-schedule-venue"><img src="/images/icons/locationIcon.png" alt="" class="artist-detail-schedule-icon" aria-hidden="true"><?= htmlspecialchars($ev->venueName . ', ' . ($ev->venueCity ?? 'Haarlem')) ?><?php if (preg_match('/^.+?[–—-]\s*(.+)$/u', $ev->title ?? '', $m) && trim($m[1])): ?> — <span class="artist-detail-schedule-type"><?= htmlspecialchars(trim($m[1])) ?></span><?php endif; ?></p>
                        <p class="artist-detail-schedule-desc"><?= htmlspecialchars($ev->description ?? '') ?></p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="artist-detail-schedule-image">
                <img src="<?= htmlspecialchars($viewModel->scheduleImage) ?>" alt="<?= htmlspecialchars($artist['name']) ?> performing" onerror="this.style.display='none'">
            </div>
        </div>
        <div class="artist-detail-schedule-cta">
            <a href="/dance/event/<?= (int) ($viewModel->artistEvents[0]->id ?? 0) ?>" class="artist-detail-schedule-btn">Tickets <span aria-hidden="true">&#8594;</span></a>
        </div>
    </section>
    <?php endif; ?>

    <?php if (count($viewModel->galleryImages) >= 4): ?>
    <!-- Gallery layout expects at least 4 items; indices 2/3 have safe fallback to index 0. -->
    <section class="artist-detail-section artist-detail-gallery-section">
        <div class="artist-detail-gallery-header">
            <h2 class="artist-detail-gallery-title">Gallery</h2>
            <span class="artist-detail-gallery-title-line" aria-hidden="true"></span>
            <p class="artist-detail-gallery-sub">Behind the scenes &amp; live moments</p>
        </div>
        <div class="artist-detail-gallery">
            <div class="artist-detail-gallery-item artist-detail-gallery-item-left">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[0]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> live" onerror="this.style.display='none'">
            </div>
            <div class="artist-detail-gallery-item artist-detail-gallery-item-right">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[1]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> backstage" onerror="this.style.display='none'">
            </div>
            <div class="artist-detail-gallery-item artist-detail-gallery-item-left">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[3] ?? $viewModel->galleryImages[0]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> stage" onerror="this.style.display='none'">
            </div>
            <div class="artist-detail-gallery-item artist-detail-gallery-item-right">
                <img src="/images/dance/<?= htmlspecialchars($viewModel->galleryImages[2] ?? $viewModel->galleryImages[0]) ?>" alt="<?= htmlspecialchars($artist['name']) ?> portrait" onerror="this.style.display='none'">
            </div>
        </div>
        <div class="artist-detail-stats">
            <?php foreach ($viewModel->galleryStats as $stat): ?>
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
