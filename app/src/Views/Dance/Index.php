<?php
$fridayEvents = $viewModel->fridayEvents;
$saturdayEvents = $viewModel->saturdayEvents;
$sundayEvents = $viewModel->sundayEvents;
// Featured: B2B2B Saturday, Armin Trance Club (Sun), Hardwell Final Night (Sun)
$featuredEvents = array_merge(
    array_slice($saturdayEvents, 0, 1),
    array_slice($sundayEvents, 1, 2)
);

$app = (new \App\Repositories\SettingsRepository())->getAll();
$danceConfig = (new \App\Repositories\DanceSettingsRepository())->getAll();
$artists = $viewModel->artists;
$danceHeroImage = '/images/dance/' . rawurlencode($danceConfig['hero_image']);
$featuredImages = $danceConfig['featured_images'];
$fridayImages = $danceConfig['friday_images'];
$saturdayImages = $danceConfig['saturday_images'];
$sundayImages = $danceConfig['sunday_images'];
$fridayGenres = $danceConfig['friday_genres'];
$saturdayGenres = $danceConfig['saturday_genres'];
$sundayGenres = $danceConfig['sunday_genres'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($viewModel->pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= htmlspecialchars($app['css_version']) ?>">
</head>
<body class="dance-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero -->
    <section class="dance-hero" style="background-image: linear-gradient(120deg, rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('<?= htmlspecialchars($danceHeroImage) ?>');">
        <div class="dance-hero-overlay"></div>
        <div class="dance-hero-content">
            <h1><?= htmlspecialchars($viewModel->pageTitle) ?></h1>
            <p class="dance-hero-subtitle">Experience Haarlem's biggest nights of house, techno, and trance.</p>
            <a href="#featured-events" class="btn btn-primary btn-white">View Dance Events</a>
        </div>
    </section>

    <!-- About Dance -->
    <section class="dance-about container">
        <nav class="breadcrumbs dance-breadcrumbs">
            <a href="/">HOME</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-current">DANCE</span>
        </nav>
        <h2 class="dance-about-heading">About Dance</h2>
        <div class="dance-about-content">
            <p>Haarlem Dance brings the world's best house, techno and trance DJs to iconic Haarlem locations.</p>
            <p>Across three nights, visitors experience Back2Back headline sets, immersive club sessions and unique experimental performances.</p>
            <p>Join thousands of music lovers for the most energetic part of the Festival.</p>
        </div>
    </section>

    <!-- Featured Events (3 cards from first 3 dance events - real data, no overrides) -->
    <section id="featured-events" class="dance-featured container">
        <h2 class="dance-section-title">Featured Events</h2>
        <div class="dance-cards dance-cards-featured">
            <?php
            $genreLabels = $danceConfig['featured_genre_labels'];
            foreach ($featuredEvents as $i => $event):
                $imagePath = '/images/dance/' . rawurlencode($featuredImages[$i] ?? $featuredImages[0]);
                $genre = $genreLabels[$i] ?? 'DANCE';
                $dayLabel = ucfirst($event->eventDay ?? 'friday');
                $timeLine = $dayLabel . ' • ' . ($event->startTime ?? '20:00');
                $title = $event->title;
                $venue = $event->venueName . ', ' . $event->venueCity;
                $desc = $event->description ?? '';
            ?>
                <a href="/dance/event/<?= (int) $event->id ?>" class="dance-card" style="text-decoration: none; color: inherit;">
                    <div class="dance-card-image-wrap">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($title) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="dance-card-placeholder" style="display:none;">📷</div>
                    </div>
                    <div class="dance-card-body">
                        <p class="dance-card-meta"><span class="dance-meta-icon">📅</span> <?= htmlspecialchars($timeLine) ?></p>
                        <p class="dance-card-meta"><span class="dance-meta-icon">📍</span> <?= htmlspecialchars($venue) ?></p>
                        <h3 class="dance-card-title"><?= htmlspecialchars($title) ?></h3>
                        <p class="dance-card-desc"><?= htmlspecialchars($desc) ?></p>
                        <span class="dance-genre-btn"><?= htmlspecialchars($genre) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- All Events -->
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
                    $imageName = $dayImages[$i % count($dayImages)] ?? $dayImages[0];
                    $imagePath = '/images/dance/' . rawurlencode($imageName);
                    $genre = $dayGenres[$i % count($dayGenres)] ?? 'DANCE';
                    $dateTime = $dayLabel . ' • ' . ($event->startTime ?? $app['default_event_time']);
                ?>
                    <a href="/dance/event/<?= (int) $event->id ?>" class="dance-card dance-card-vertical" style="text-decoration: none; color: inherit;">
                        <div class="dance-card-image-wrap">
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="dance-card-placeholder" style="display:none;">📷</div>
                        </div>
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

    <!-- Artists -->
    <section class="dance-artists container">
        <h2 class="dance-section-title">Artist(s)</h2>
        <div class="dance-artists-grid">
            <?php foreach ($artists as $artist):
                $artistImagePath = '/images/dance/' . rawurlencode($artist['image']);
            ?>
            <article class="dance-artist-card dance-artist-card-with-image">
                <div class="dance-artist-card-image-wrap">
                    <img src="<?= htmlspecialchars($artistImagePath) ?>" alt="<?= htmlspecialchars($artist['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="dance-artist-card-placeholder" style="display:none;">🎤</div>
                </div>
                <div class="dance-artist-card-body">
                    <h3 class="dance-artist-name"><?= htmlspecialchars($artist['name']) ?></h3>
                    <p class="dance-artist-bio"><?= htmlspecialchars($artist['bio']) ?></p>
                    <a href="<?= !empty($artist['slug']) ? '/dance/artist/' . htmlspecialchars($artist['slug']) : '#' ?>" class="dance-artist-info-link">INFO &gt;</a>
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
