<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = $vm->appSettings;

if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$selectedDay = $vm->selectedDay ?? 'all';
$selectedDay = $vm->selectedDay ?? 'all';
$heroImages = $vm->getEventsHeroImages();
$heroImage1 = $heroImages[0] ?? '';
$heroImage2 = $heroImages[1] ?? '';
$mapLocations = $vm->getEventsMapLocations();

// Settings for the page title, styles, body class
$pageTitle = $vm->pageTitle ?? 'Events - Haarlem Stories';
$pageStyles = ['/css/pages/stories.css'];
$bodyClass = 'stories-page stories-events-page';

// Hero settings
$pageHeroTitle = 'Events of Haarlem Stories';
$pageHeroSubtitle = 'Discover the best of Haarlem through various storytelling experiences. Check age labels for each event.';
$pageHeroImage = '/images/Stories/stories-events.jpg';
$pageHeroAlt = 'Events of Haarlem Stories';
$pageHeroClass = 'stories-events-hero';
$pageHeroContentClass = 'stories-events-hero__content';

// Breadcrumbs
$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Stories', 'url' => '/stories'],
        ['label' => 'Events', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= htmlspecialchars($bodyClass) ?>">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Hero -->
    <?php require __DIR__ . '/../partials/page-hero.php'; ?>

    <!-- Breadcrumb -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <section class="container schedule-section" aria-label="Day filter">
        <div class="schedule-row">
            <div class="schedule-label"><?= h($vm->settings['events_schedule_label'] ?? 'Select the Day:') ?></div>
            <div class="schedule-content">
                <div class="filter-tabs stories-day-tabs">
                    <?php
                    $days = [
                        'all' => 'All Events >',
                        'thursday' => 'Thursday >',
                        'friday' => 'Friday >',
                        'saturday' => 'Saturday >',
                        'sunday' => 'Sunday >',
                    ];
                    foreach ($days as $key => $label): ?>
                        <a class="filter-tab stories-day-tab <?= $vm->isActive($key) ? 'active' : '' ?>"
                           href="/stories/events?day=<?= h($key) ?>"
                           aria-current="<?= $vm->isActive($key) ? 'page' : 'false' ?>">
                            <?= h($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="container stories-cards" aria-label="Story cards">
        <div class="stories-cards-grid" id="storiesGrid">
            <div class="cards-loading copy-text copy-text--muted" id="cardsLoading">
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
