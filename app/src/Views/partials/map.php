<?php
$mapLocations = [
    ['name' => 'Lichtfabriek',     'lat' => 52.3920, 'lng' => 4.6520, 'layer' => 'Music & Nightlife'],
    ['name' => 'Patronaat',        'lat' => 52.3810, 'lng' => 4.6330, 'layer' => 'Music & Nightlife'],
    ['name' => 'Schuur',           'lat' => 52.3845, 'lng' => 4.6280, 'layer' => 'Music & Nightlife'],
    ['name' => 'Elswout Theater',  'lat' => 52.3780, 'lng' => 4.6180, 'layer' => 'Music & Nightlife'],
    ['name' => 'Café XO',          'lat' => 52.3870, 'lng' => 4.6390, 'layer' => 'Cafés & Food'],
    ['name' => 'Café de Roemer',   'lat' => 52.3865, 'lng' => 4.6385, 'layer' => 'Cafés & Food'],
    ['name' => 'Grand Café Brinkmann', 'lat' => 52.3872, 'lng' => 4.6400, 'layer' => 'Cafés & Food'],
    ['name' => 'Kweekcafé',        'lat' => 52.3850, 'lng' => 4.6310, 'layer' => 'Cafés & Food'],
];
$mapLayers = array_unique(array_column($mapLocations, 'layer'));
?>
<section class="map-section">
    <div class="container">
        <h2 class="section-title section-title--underlined map-heading">Festival Locations — Haarlem City Map</h2>
        <p class="map-intro">
            Explore the key festival venues spread across the historic center of Haarlem. From iconic squares to intimate cultural spaces, each location hosts unique performances, workshops, and experiences throughout the festival days.
        </p>
        <div class="map-wrapper" id="festival-map-wrapper">
            <div id="festival-map" class="map-iframe"></div>
            <div class="map-layer-legend">
                <span class="map-layer-title">Filter by category</span>
                <p class="map-layer-hint">Toggle layers to show or hide venues.</p>
                <div class="map-layer-toggles">
                    <button type="button" class="map-layer-btn map-layer-btn-all" aria-label="Show all layers">All</button>
                    <button type="button" class="map-layer-btn map-layer-btn-none" aria-label="Hide all layers">None</button>
                </div>
                <div class="map-layer-list">
                    <?php foreach ($mapLayers as $layer): ?>
                        <label class="map-layer-check">
                            <input type="checkbox" class="map-layer-input" value="<?= htmlspecialchars($layer) ?>" checked>
                            <span class="map-layer-checkbox"></span>
                            <span class="map-layer-label"><?= htmlspecialchars($layer) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="map-actions">
            <button type="button" class="btn btn--light" id="map-center-btn" aria-label="Center map on all venues">Center map</button>
            <a href="https://www.google.com/maps/search/festival+haarlem" target="_blank" rel="noopener" class="btn btn--light">View live map →</a>
        </div>
    </div>
</section>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var locations = <?= json_encode($mapLocations) ?>;
    var wrapper = document.getElementById('festival-map-wrapper');
    if (!wrapper || !locations.length) return;
    var map = L.map('festival-map').setView([52.3874, 4.6368], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' }).addTo(map);
    var layerToLayer = {};
    var allMarkers = [];
    locations.forEach(function(loc) {
        var layerName = loc.layer;
        if (!layerToLayer[layerName]) layerToLayer[layerName] = L.layerGroup().addTo(map);
        var m = L.marker([loc.lat, loc.lng]).bindPopup('<strong>' + (loc.name || '') + '</strong>').addTo(layerToLayer[layerName]);
        allMarkers.push(m);
    });
    function updateLayer(name, show) {
        if (!layerToLayer[name]) return;
        if (show) map.addLayer(layerToLayer[name]); else map.removeLayer(layerToLayer[name]);
    }
    function setCheckbox(layerName, checked) {
        document.querySelectorAll('.map-layer-input').forEach(function(cb) {
            if (cb.value === layerName) cb.checked = checked;
        });
    }
    document.querySelectorAll('.map-layer-input').forEach(function(cb) {
        cb.addEventListener('change', function() { updateLayer(this.value, this.checked); });
    });
    document.querySelector('.map-layer-btn-all')?.addEventListener('click', function() {
        Object.keys(layerToLayer).forEach(function(name) { updateLayer(name, true); setCheckbox(name, true); });
    });
    document.querySelector('.map-layer-btn-none')?.addEventListener('click', function() {
        Object.keys(layerToLayer).forEach(function(name) { updateLayer(name, false); setCheckbox(name, false); });
    });
    document.getElementById('map-center-btn')?.addEventListener('click', function() {
        if (allMarkers.length === 0) return;
        var g = L.featureGroup(allMarkers);
        map.fitBounds(g.getBounds().pad(0.15));
    });
})();
</script>
