<section class="history-tours-map">
    <div class="container">
        <h2 class="section-title history-tours-section-title">ROUTE MAP</h2>
        <div id="history-tours-leaflet-map" class="history-tours-leaflet-map"></div>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
        var map = L.map('history-tours-leaflet-map').setView([52.3816, 4.6368], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Pass locations from PHP to JS
        var locations = <?= json_encode(
            array_map(fn($loc) => [
                'name' => $loc->name,
                'lat' => $loc->lat,
                'lng' => $loc->lng,
                'slug' => $loc->slug,
            ], array_filter($mapLocations, fn($loc) => $loc->lat && $loc->lng))
        ) ?>;

        // Add marker for each location
        locations.forEach(function (loc, index) {
            var customIcon = L.divIcon({
                className: 'history-tours-marker-wrapper',
                html: '<div class="section-subtitle history-tours-map-marker">' + (index + 1) + '</div>',
                iconSize: [32, 32],
                iconAnchor: [16, 16],
            });

            L.marker([loc.lat, loc.lng], {icon: customIcon})
                .bindPopup('<strong>' + loc.name + '</strong><br><a href="/history/location/' + loc.slug + '">Read more →</a>')
                .addTo(map);
        });
    })();
</script>