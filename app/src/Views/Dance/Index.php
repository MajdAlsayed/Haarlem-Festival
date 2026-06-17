<?php
/** Public Dance index; card images come from merged CMS settings and dance.php defaults. */
/** @var \App\ViewModels\DanceViewModel $vm */
$fridayEvents = $vm->fridayEvents;
$saturdayEvents = $vm->saturdayEvents;
$sundayEvents = $vm->sundayEvents;
$featuredEvents = $vm->featuredEvents;
$artists = $vm->artists;
$appSettings = $vm->appSettings;
$danceSettings = $vm->danceSettings;
$breadcrumbs = $vm->breadcrumbs;

// small readers so the rest of the file stays clean and readable
$danceList = function (string $key) use ($danceSettings): array {
    if (isset($danceSettings[$key]) && is_array($danceSettings[$key])) {
        return $danceSettings[$key];
    }
    return [];
};
$text = function (array $row, string $key): string {
    if (isset($row[$key])) {
        return (string) $row[$key];
    }
    return '';
};
$setting = function (string $key, string $fallback = '') use ($danceSettings): string {
    if (isset($danceSettings[$key]) && (string) $danceSettings[$key] !== '') {
        return (string) $danceSettings[$key];
    }
    return $fallback;
};
$danceImage = function (string $name): string {
    if ($name === '') {
        return '';
    }
    return '/images/dance/' . rawurlencode($name);
};

// section copy, all coming from the CMS
$aboutHeading = $setting('about_section_heading');
$featuredTitle = $setting('featured_section_title');
$allEventsTitle = $setting('all_events_section_title');
$artistsTitle = $setting('artists_section_title');
$artistInfoLabel = $setting('artist_info_label');
$showMoreLabel = $setting('show_more_artists_label');

$dayLabels = [
    'friday' => $setting('day_label_friday'),
    'saturday' => $setting('day_label_saturday'),
    'sunday' => $setting('day_label_sunday'),
];

// images and genres for the cards
$featuredImages = $danceList('featured_images');
$genreLabels = $danceList('featured_genre_labels');
$aboutParagraphs = $danceList('about_paragraphs');
$defaultEventTime = $text($appSettings, 'default_event_time');

// page setup
$pageTitle = $vm->pageTitle;
$pageStyles = ['/css/pages/dance.css'];
$bodyClass = 'dance-page';

// hero values read by the festival-hero partial
$heroModifier = 'festival-hero--dance';
$heroTitle = $setting('hero_title', 'Dance');
$heroImageAlt = $heroTitle;
$heroImage = $danceImage($setting('hero_image'));
$heroSubtitle = $setting('hero_subtitle');
$heroButtonText = $setting('hero_button_text', 'Explore events');
$heroButtonUrl = $setting('hero_button_url', '#featured-events');
$heroButtonClass = 'btn btn--light';
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero -->
    <?php require __DIR__ . '/../partials/festival-hero.php'; ?>

    <!-- Breadcrumbs nav -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <section class="dance-about container">
        <h2 class="section-title section-title--accent section-title--underlined"><?= htmlspecialchars($aboutHeading) ?></h2>
        <div class="copy-text dance-about-content">
            <?php foreach ($aboutParagraphs as $para): ?>
                <?php if (is_string($para) && !\App\Core\HtmlSanitizer::isEmptyHtml($para)): ?>
                    <div class="cms-html"><?= \App\Core\HtmlSanitizer::purify($para) ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="featured-events" class="dance-featured container">
        <h2 class="section-title section-title--underlined dance-section-title"><?= htmlspecialchars($featuredTitle) ?></h2>
        <div class="dance-cards dance-cards-featured">
            <?php foreach ($featuredEvents as $i => $event): ?>
                <?php
                // each card can reuse the first image/genre if the CMS has fewer entries than events
                $imageName = '';
                if (isset($featuredImages[$i])) {
                    $imageName = $featuredImages[$i];
                } elseif (isset($featuredImages[0])) {
                    $imageName = $featuredImages[0];
                }
                $imagePath = $danceImage($imageName);

                $genre = '';
                if (isset($genreLabels[$i])) {
                    $genre = $genreLabels[$i];
                }

                $eventDay = $event->eventDay;
                if ($eventDay === null || $eventDay === '') {
                    $eventDay = 'friday';
                }
                $startTime = $event->startTime;
                if ($startTime === null || $startTime === '') {
                    $startTime = '20:00';
                }
                $timeLine = ucfirst($eventDay) . ' • ' . $startTime;
                $venue = $event->venueName . ', ' . $event->venueCity;
                ?>
                <a href="/dance/event/<?= (int) $event->id ?>" class="festival-card festival-card--dance-event dance-card u-plain-link">
                    <?php if ($imagePath !== ''): ?>
                    <div class="dance-card-image-wrap">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="dance-card-placeholder is-hidden">&#128247;</div>
                    </div>
                    <?php endif; ?>
                    <div class="dance-card-body">
                        <p class="copy-text copy-text--sm dance-card-meta"><span class="dance-meta-icon">&#128197;</span> <?= htmlspecialchars($timeLine) ?></p>
                        <p class="copy-text copy-text--sm dance-card-meta"><span class="dance-meta-icon">&#128205;</span> <?= htmlspecialchars($venue) ?></p>
                        <h3 class="dance-card-title"><?= htmlspecialchars($event->title) ?></h3>
                        <p class="copy-text copy-text--sm dance-card-desc"><?= htmlspecialchars((string) $event->description) ?></p>
                        <span class="dance-genre-badge"><?= htmlspecialchars($genre) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="dance-all container">
        <h2 class="section-title section-title--underlined dance-section-title"><?= htmlspecialchars($allEventsTitle) ?></h2>
        <div class="filter-tabs dance-date-filters" role="tablist">
            <button type="button" class="filter-tab active" data-filter="friday" aria-pressed="true"><?= htmlspecialchars($dayLabels['friday']) ?></button>
            <button type="button" class="filter-tab" data-filter="saturday" aria-pressed="false"><?= htmlspecialchars($dayLabels['saturday']) ?></button>
            <button type="button" class="filter-tab" data-filter="sunday" aria-pressed="false"><?= htmlspecialchars($dayLabels['sunday']) ?></button>
        </div>

        <?php
        $panels = [
            'friday' => ['events' => $fridayEvents, 'images' => $danceList('friday_images'), 'genres' => $danceList('friday_genres')],
            'saturday' => ['events' => $saturdayEvents, 'images' => $danceList('saturday_images'), 'genres' => $danceList('saturday_genres')],
            'sunday' => ['events' => $sundayEvents, 'images' => $danceList('sunday_images'), 'genres' => $danceList('sunday_genres')],
        ];
        // one panel per day; the JS tab switcher toggles the active class
        foreach ($panels as $day => $dayData):
            $dayEvents = $dayData['events'];
            $dayImages = $dayData['images'];
            $dayGenres = $dayData['genres'];
            $dayLabel = $dayLabels[$day];
            $panelClass = 'dance-events-panel';
            if ($day === 'friday') {
                $panelClass = 'dance-events-panel active';
            }
        ?>
        <div class="<?= $panelClass ?>" data-filter="<?= htmlspecialchars($day) ?>" role="tabpanel">
            <div class="dance-cards dance-cards-grid">
                <?php foreach ($dayEvents as $i => $event):
                    // rotate through the day images/genres when there are more events than entries
                    $imageName = '';
                    if (!empty($dayImages)) {
                        $imageName = $dayImages[$i % count($dayImages)];
                    }
                    $imagePath = $danceImage($imageName);

                    $genre = '';
                    if (!empty($dayGenres)) {
                        $genre = $dayGenres[$i % count($dayGenres)];
                    }

                    $startTime = $event->startTime;
                    if ($startTime === null || $startTime === '') {
                        $startTime = $defaultEventTime;
                    }
                    $dateTime = $dayLabel . ' • ' . $startTime;
                ?>
                    <a href="/dance/event/<?= (int) $event->id ?>" class="festival-card festival-card--dance-event dance-card dance-card-vertical u-plain-link">
                        <?php if ($imagePath !== ''): ?>
                        <div class="dance-card-image-wrap">
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($event->title) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="dance-card-placeholder is-hidden">&#128247;</div>
                        </div>
                        <?php endif; ?>
                        <div class="dance-card-body dance-card-body-stack">
                            <p class="copy-text copy-text--sm dance-card-venue"><?= htmlspecialchars($event->venueName) ?>, <?= htmlspecialchars($event->venueCity) ?></p>
                            <h3 class="dance-card-title"><?= htmlspecialchars($event->title) ?></h3>
                            <p class="copy-text copy-text--sm dance-card-datetime"><?= htmlspecialchars($dateTime) ?></p>
                            <p class="copy-text copy-text--sm dance-card-desc"><?= htmlspecialchars((string) $event->description) ?></p>
                            <span class="dance-genre-badge"><?= htmlspecialchars($genre) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <section id="dance-artists" class="dance-artists container">
        <h2 class="section-title section-title--underlined dance-section-title"><?= htmlspecialchars($artistsTitle) ?></h2>
        <div class="dance-artists-grid">
            <?php foreach ($artists as $artist): ?>
                <?php
                $artistName = $text($artist, 'name');
                $artistBio = $text($artist, 'bio');
                $artistSlug = $text($artist, 'slug');
                $artistImagePath = $danceImage($text($artist, 'image'));

                $artistUrl = '#';
                if ($artistSlug !== '') {
                    $artistUrl = '/dance/artist/' . rawurlencode($artistSlug);
                }
                ?>
            <article class="festival-card festival-card--dance-artist dance-artist-card">
                <?php if ($artistImagePath !== ''): ?>
                <div class="dance-artist-card-image-wrap">
                    <img src="<?= htmlspecialchars($artistImagePath) ?>" alt="<?= htmlspecialchars($artistName) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="dance-artist-card-placeholder is-hidden">&#127908;</div>
                </div>
                <?php endif; ?>
                <div class="dance-artist-card-body">
                    <h3 class="section-subtitle dance-artist-name"><?= htmlspecialchars($artistName) ?></h3>
                    <p class="copy-text copy-text--sm dance-artist-bio"><?= htmlspecialchars($artistBio) ?></p>
                    <a href="<?= htmlspecialchars($artistUrl) ?>" class="btn btn--outline btn--sm dance-artist-info-link"><?= htmlspecialchars($artistInfoLabel) ?></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn--outline dance-show-more"><?= htmlspecialchars($showMoreLabel) ?></button>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function() {
    var filters = document.querySelectorAll('.filter-tab');
    var panels = document.querySelectorAll('.dance-events-panel');
    filters.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.getAttribute('data-filter');
            filters.forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            // Show only the selected day panel while keeping markup static.
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
