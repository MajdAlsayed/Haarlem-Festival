<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = $vm->appSettings;

if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$selectedDay = $vm->selectedDay ?? 'all';
$heroImages = $vm->getEventsHeroImages();
$heroImage1 = $heroImages[0] ?? '';
$heroImage2 = $heroImages[1] ?? '';
$mapLocations = $vm->getEventsMapLocations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($vm->pageTitle ?? 'Events - Haarlem Stories') ?></title>

    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/Stories/events.css?v=<?= h($app['css_version'] ?? '1') ?>-eventmain">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>

<body class="stories-events-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <section class="stories-hero-banner" aria-label="Events hero images">
        <div class="hero-img hero-img-left" style="background-image: url('<?= h($heroImage1) ?>');" role="img" aria-label="Events banner image 1"></div>
        <div class="hero-img hero-img-right" style="background-image: url('<?= h($heroImage2) ?>');" role="img" aria-label="Events banner image 2"></div>
        <div class="hero-overlay">
            <h1><?= h($vm->settings['events_hero_heading'] ?? 'The Event of Haarlem Stories') ?></h1>
            <p><?= h($vm->settings['events_hero_tagline'] ?? '') ?></p>
        </div>
    </section>

    <nav class="stories-breadcrumb" aria-label="Breadcrumb">
        <div class="stories-breadcrumb-inner">
            <a href="/" class="stories-breadcrumb-link">HOME</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">&rarr;</span>
            <a href="/stories" class="stories-breadcrumb-link">STORIES</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">&rarr;</span>
            <span class="stories-breadcrumb-link active" aria-current="page">EVENTS</span>
        </div>
    </nav>

    <section class="stories-heading-section">
        <div class="stories-heading-inner">
            <h2 class="stories-heading-title"><?= h($vm->settings['events_intro_heading'] ?? 'Discover Stories Across Haarlem') ?></h2>
            <p class="stories-heading-text">
                <?= h($vm->settings['events_intro_text'] ?? '') ?>
            </p>
        </div>
    </section>

    <section class="schedule-section" aria-label="Day filter">
        <div class="schedule-row">
            <div class="schedule-label"><?= h($vm->settings['events_schedule_label'] ?? 'Select the Day:') ?></div>
            <div class="schedule-content">
                <div class="day-tabs day-tabs-inline">
                    <?php
                    $days = [
                        'all' => 'All Events >',
                        'thursday' => 'Thursday >',
                        'friday' => 'Friday >',
                        'saturday' => 'Saturday >',
                        'sunday' => 'Sunday >',
                    ];
                    foreach ($days as $key => $label): ?>
                        <a class="day-tab <?= $vm->isActive($key) ? 'active' : '' ?>"
                           href="/stories/events?day=<?= h($key) ?>"
                           aria-current="<?= $vm->isActive($key) ? 'page' : 'false' ?>">
                            <?= h($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="stories-cards" aria-label="Story cards">
        <div class="stories-cards-grid" id="storiesGrid">
            <div class="cards-loading" id="cardsLoading">
                <div class="cards-loading-spinner" aria-hidden="true"></div>
                Loading stories...
            </div>
        </div>
    </section>

    <section class="stories-map-section" aria-label="Stories event locations">
        <h2 class="stories-map-title"><?= h($vm->settings['events_map_title'] ?? 'Places To Visit For Events') ?></h2>
        <p class="stories-map-subtitle"><?= h($vm->settings['events_map_subtitle'] ?? '') ?></p>

        <div class="map-venue-buttons" aria-label="Map venue shortcuts">
            <?php foreach ($mapLocations as $index => $location): ?>
                <button
                    type="button"
                    class="map-venue-btn <?= $index === 0 ? 'active' : '' ?>"
                    data-map-index="<?= (int)$index ?>">
                    <span aria-hidden="true">O</span>
                    <?= h($location['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="stories-map-frame">
            <div id="storiesEventsMap" class="stories-events-map"></div>
        </div>

        <a class="btn-map" href="/stories">&lsaquo; BACK</a>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<!-- NOTE: not in course slides (DOMPurify), but required for safe API content before innerHTML. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.5/purify.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
window.STORIES_DAY = <?= json_encode($selectedDay) ?>;
</script>
<script src="/assets/js/stories.js"></script>

<script>
(function () {
    var mapElement = document.getElementById('storiesEventsMap');

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    // NOTE: not in course slides (Leaflet map), kept for the existing map feature.
    var locations = <?= json_encode($mapLocations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var map = L.map('storiesEventsMap').setView([52.389, 4.637], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markers = [];
    for (var i = 0; i < locations.length; i++) {
        var loc = locations[i];
        var icon = L.divIcon({
            className: 'stories-map-marker-wrapper',
            html: '<div class="stories-map-marker">' + (i + 1) + '</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        markers.push(L.marker([loc.lat, loc.lng], { icon: icon })
            .bindPopup('<strong>' + loc.name + '</strong><br>' + loc.label)
            .addTo(map));
    }

    if (markers.length > 1) {
        var group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.25));
    }

    var buttons = document.getElementsByClassName('map-venue-btn');
    for (var b = 0; b < buttons.length; b++) {
        buttons[b].addEventListener('click', function () {
            var index = parseInt(this.getAttribute('data-map-index'), 10);
            var selectedLocation = locations[index];

            if (!selectedLocation || !markers[index]) {
                return;
            }

            for (var j = 0; j < buttons.length; j++) {
                buttons[j].classList.remove('active');
            }

            this.classList.add('active');
            map.setView([selectedLocation.lat, selectedLocation.lng], 16);
            markers[index].openPopup();
        });
    }
})();
</script>
</body>
</html>
