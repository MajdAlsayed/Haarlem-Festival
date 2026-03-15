<?php
/** @var \App\ViewModels\DanceViewModel $viewModel */
$fridayEvents = $viewModel->fridayEvents;
$saturdayEvents = $viewModel->saturdayEvents;
$sundayEvents = $viewModel->sundayEvents;
$featuredEvents = $viewModel->featuredEvents;
$artists = $viewModel->artists;
$appSettings = $viewModel->appSettings;
$danceSettings = $viewModel->danceSettings;
$breadcrumbs = $viewModel->breadcrumbs;

$danceHeroImage = isset($danceSettings['hero_image']) ? '/images/dance/' . rawurlencode((string) $danceSettings['hero_image']) : '/images/dance/Dance page front picture.png';
$featuredImages = isset($danceSettings['featured_images']) && is_array($danceSettings['featured_images']) ? $danceSettings['featured_images'] : [];
$fridayImages = isset($danceSettings['friday_images']) && is_array($danceSettings['friday_images']) ? $danceSettings['friday_images'] : [];
$saturdayImages = isset($danceSettings['saturday_images']) && is_array($danceSettings['saturday_images']) ? $danceSettings['saturday_images'] : [];
$sundayImages = isset($danceSettings['sunday_images']) && is_array($danceSettings['sunday_images']) ? $danceSettings['sunday_images'] : [];
$fridayGenres = isset($danceSettings['friday_genres']) && is_array($danceSettings['friday_genres']) ? $danceSettings['friday_genres'] : [];
$saturdayGenres = isset($danceSettings['saturday_genres']) && is_array($danceSettings['saturday_genres']) ? $danceSettings['saturday_genres'] : [];
$sundayGenres = isset($danceSettings['sunday_genres']) && is_array($danceSettings['sunday_genres']) ? $danceSettings['sunday_genres'] : [];
$genreLabels = isset($danceSettings['featured_genre_labels']) && is_array($danceSettings['featured_genre_labels']) ? $danceSettings['featured_genre_labels'] : ['DANCE', 'DANCE', 'DANCE'];
$defaultEventTime = isset($appSettings['default_event_time']) ? (string) $appSettings['default_event_time'] : '22:00';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($appSettings['css_version'] ?? '1') ?>">
</head>
<body class="dance-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="dance-hero" style="background-image: linear-gradient(120deg, rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('<?= htmlspecialchars($danceHeroImage) ?>');">
        <div class="dance-hero-overlay"></div>
        <div class="dance-hero-content">
            <h1><?= htmlspecialchars($viewModel->pageTitle) ?></h1>
            <p class="dance-hero-subtitle">Experience Haarlem's biggest nights of house, techno, and trance.</p>
            <a href="#featured-events" class="btn btn-primary btn-white">View Dance Events</a>
        </div>
    </section>

    <section class="dance-about container">
        <nav class="breadcrumbs dance-breadcrumbs" aria-label="Breadcrumb">
            <?php foreach ($breadcrumbs as $i => $crumb): ?>
                <?php if ($i > 0): ?><span class="breadcrumb-sep">›</span><?php endif; ?>
                <?php if (!empty($crumb['url'])): ?>
                    <a href="<?= htmlspecialchars($crumb['url']) ?>"><?= htmlspecialchars($crumb['label']) ?></a>
                <?php else: ?>
                    <span class="breadcrumb-current"><?= htmlspecialchars($crumb['label']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <h2 class="dance-about-heading">About Dance</h2>
        <div class="dance-about-content">
            <p>Haarlem Dance brings the world's best house, techno and trance DJs to iconic Haarlem locations.</p>
            <p>Across three nights, visitors experience Back2Back headline sets, immersive club sessions and unique experimental performances.</p>
            <p>Join thousands of music lovers for the most energetic part of the Festival.</p>
        </div>
    </section>

    <section id="featured-events" class="dance-featured container">
        <h2 class="dance-section-title">Featured Events</h2>
        <div class="dance-cards dance-cards-featured">
            <?php foreach ($featuredEvents as $i => $event): ?>
                <?php
                $imageName = isset($featuredImages[$i]) ? $featuredImages[$i] : (isset($featuredImages[0]) ? $featuredImages[0] : '');
                $imagePath = $imageName !== '' ? '/images/dance/' . rawurlencode($imageName) : '';
                $genre = isset($genreLabels[$i]) ? $genreLabels[$i] : 'DANCE';
                $dayLabel = ucfirst($event->eventDay ?? 'friday');
                $timeLine = $dayLabel . ' • ' . ($event->startTime ?? '20:00');
                $title = $event->title;
                $venue = $event->venueName . ', ' . $event->venueCity;
                $desc = $event->description ?? '';
                ?>
                <a href="/dance/event/<?= (int) $event->id ?>" class="dance-card" style="text-decoration: none; color: inherit;">
                    <?php if ($imagePath !== ''): ?>
                    <div class="dance-card-image-wrap">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($title) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="dance-card-placeholder" style="display:none;">&#128247;</div>
                    </div>
                    <?php endif; ?>
                    <div class="dance-card-body">
                        <p class="dance-card-meta"><span class="dance-meta-icon">&#128197;</span> <?= htmlspecialchars($timeLine) ?></p>
                        <p class="dance-card-meta"><span class="dance-meta-icon">&#128205;</span> <?= htmlspecialchars($venue) ?></p>
                        <h3 class="dance-card-title"><?= htmlspecialchars($title) ?></h3>
                        <p class="dance-card-desc"><?= htmlspecialchars($desc) ?></p>
                        <span class="dance-genre-btn"><?= htmlspecialchars($genre) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="dance-all container">
        <h2 class="dance-section-title">All Events</h2>
        <div class="dance-date-filters" role="tablist">
            <button type="button" class="dance-filter-btn active" data-filter="friday" aria-pressed="true">Friday</button>
            <button type="button" class="dance-filter-btn" data-filter="saturday" aria-pressed="false">Saturday</button>
            <button type="button" class="dance-filter-btn" data-filter="sunday" aria-pressed="false">Sunday</button>
        </div>

        <?php
        $dayLabels = ['friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
        $panels = [
            'friday' => ['events' => $fridayEvents, 'images' => $fridayImages, 'genres' => $fridayGenres],
            'saturday' => ['events' => $saturdayEvents, 'images' => $saturdayImages, 'genres' => $saturdayGenres],
            'sunday' => ['events' => $sundayEvents, 'images' => $sundayImages, 'genres' => $sundayGenres],
        ];
        foreach ($panels as $day => $data):
            $dayEvents = $data['events'];
            $dayImages = $data['images'];
            $dayGenres = $data['genres'];
            $dayLabel = $dayLabels[$day];
            $panelClass = $day === 'friday' ? 'dance-events-panel active' : 'dance-events-panel';
        ?>
        <div class="<?= $panelClass ?>" data-filter="<?= htmlspecialchars($day) ?>" role="tabpanel">
            <div class="dance-cards dance-cards-grid">
                <?php foreach ($dayEvents as $i => $event):
                    $imageName = !empty($dayImages) ? $dayImages[$i % count($dayImages)] : '';
                    $imagePath = $imageName !== '' ? '/images/dance/' . rawurlencode($imageName) : '';
                    $genre = !empty($dayGenres) ? $dayGenres[$i % count($dayGenres)] : 'DANCE';
                    $dateTime = $dayLabel . ' • ' . ($event->startTime ?? $defaultEventTime);
                ?>
                    <a href="/dance/event/<?= (int) $event->id ?>" class="dance-card dance-card-vertical" style="text-decoration: none; color: inherit;">
                        <?php if ($imagePath !== ''): ?>
                        <div class="dance-card-image-wrap">
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="dance-card-placeholder" style="display:none;">&#128247;</div>
                        </div>
                        <?php endif; ?>
                        <div class="dance-card-body dance-card-body-stack">
                            <p class="dance-card-venue"><?= htmlspecialchars($event->venueName) ?>, <?= htmlspecialchars($event->venueCity) ?></p>
                            <h3 class="dance-card-title"><?= htmlspecialchars($event->title) ?></h3>
                            <p class="dance-card-datetime"><?= htmlspecialchars($dateTime) ?></p>
                            <p class="dance-card-desc"><?= htmlspecialchars($event->description ?? '') ?></p>
                            <span class="dance-genre-pill"><?= htmlspecialchars($genre) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <section class="dance-artists container">
        <h2 class="dance-section-title">Artist(s)</h2>
        <div class="dance-artists-grid">
            <?php foreach ($artists as $artist): ?>
                <?php
                $artistImage = isset($artist['image']) ? (string) $artist['image'] : '';
                $artistImagePath = $artistImage !== '' ? '/images/dance/' . rawurlencode($artistImage) : '';
                $artistName = isset($artist['name']) ? (string) $artist['name'] : '';
                $artistBio = isset($artist['bio']) ? (string) $artist['bio'] : '';
                $artistSlug = isset($artist['slug']) ? (string) $artist['slug'] : '';
                $artistUrl = $artistSlug !== '' ? '/dance/artist/' . htmlspecialchars($artistSlug) : '#';
                ?>
            <article class="dance-artist-card dance-artist-card-with-image">
                <?php if ($artistImagePath !== ''): ?>
                <div class="dance-artist-card-image-wrap">
                    <img src="<?= htmlspecialchars($artistImagePath) ?>" alt="<?= htmlspecialchars($artistName) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="dance-artist-card-placeholder" style="display:none;">&#127908;</div>
                </div>
                <?php endif; ?>
                <div class="dance-artist-card-body">
                    <h3 class="dance-artist-name"><?= htmlspecialchars($artistName) ?></h3>
                    <p class="dance-artist-bio"><?= htmlspecialchars($artistBio) ?></p>
                    <a href="<?= $artistUrl ?>" class="dance-artist-info-link">INFO &gt;</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-outline dance-show-more">Show More Artists &gt;</button>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function() {
    var filters = document.querySelectorAll('.dance-filter-btn');
    var panels = document.querySelectorAll('.dance-events-panel');
    filters.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.getAttribute('data-filter');
            filters.forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            panels.forEach(function(p) {
                if (p.getAttribute('data-filter') === filter) {
                    p.classList.add('active');
                } else {
                    p.classList.remove('active');
                }
            });
        });
    });
})();
</script>
</body>
</html>
