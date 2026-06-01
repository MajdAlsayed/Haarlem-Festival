<?php
$storiesMapLocations = [
    ['name' => 'Verhalenhuis Haarlem', 'lat' => 52.40385, 'lng' => 4.64628],
    ['name' => 'Schuur',              'lat' => 52.3818,  'lng' => 4.63931],
    ['name' => 'Kweekcafé',           'lat' => 52.39613, 'lng' => 4.63569],
    ['name' => 'Ten Boom Museum',     'lat' => 52.38227, 'lng' => 4.6354],
    ['name' => 'Elswout Theater',     'lat' => 52.37631, 'lng' => 4.59906],
];
?>

<section class="map-section stories-map-section">
    <div class="container">
        <h2 class="section-title section-title--underlined">
            Places To Visit For Events
        </h2>

        <p class="map-intro section-subtitle">
            Explore the Stories venues in and around Haarlem.
        </p>

        <div class="map-wrapper stories-map-wrapper" id="stories-map-wrapper">
            <div id="stories-map" class="map-iframe stories-map"></div>

            <aside class="map-layer-legend stories-map-locations">
                <span class="map-layer-title">Stories locations</span>

                <ul class="stories-map-location-list">
                    <?php foreach ($storiesMapLocations as $location): ?>
                        <li><?= htmlspecialchars($location['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>

        <div class="map-actions">
            <button type="button" class="btn btn--light btn--sm" id="stories-map-center-btn" aria-label="Center map on all Stories venues">
                Center map
            </button>

            <a href="https://www.google.com/maps/search/stories+venues+haarlem"
               target="_blank"
               rel="noopener"
               class="btn btn--light btn--sm">
                View live map →
            </a>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    (function() {
        var locations = <?= json_encode($storiesMapLocations) ?>;
        var mapElement = document.getElementById('stories-map');

        if (!mapElement || !locations.length) return;

        var map = L.map('stories-map').setView([52.3874, 4.6368], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var markers = locations.map(function(location) {
            return L.marker([location.lat, location.lng])
                .bindPopup('<strong>' + location.name + '</strong>')
                .addTo(map);
        });

        document.getElementById('stories-map-center-btn')?.addEventListener('click', function() {
            var group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        });
    })();
</script>