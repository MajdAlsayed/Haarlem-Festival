<?php
// app/src/Views/partials/festivalMap.php

// Your Google My Maps base link (viewer)
$baseMapUrl = "https://www.google.com/maps/d/u/0/viewer?mid=1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw";

// Small helper
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// If you want, later you can load these from DB (venues table) when you add lat/lng columns.
// For now we keep a clean manual list to match your venues.
$places = [
  ['label' => 'All Venues', 'll' => '52.387877486255384,4.630817341343083', 'z' => 14],

  // Stories venues you added:
  ['label' => 'Verhalenhuis Haarlem', 'll' => '52.3839,4.6351', 'z' => 16],
  ['label' => 'De Schuur',            'll' => '52.3819,4.6296', 'z' => 16],
  ['label' => 'Kweekcafe',            'll' => '52.4015,4.6274', 'z' => 16],
  ['label' => 'Corrie ten Boomhuis',  'll' => '52.3816,4.6359', 'z' => 16],
  ['label' => 'Theater Elswout',       'll' => '52.3990,4.6316', 'z' => 16],
];

// Default map view
$default = $places[0];
$defaultUrl = $baseMapUrl . "&ll=" . urlencode($default['ll']) . "&z=" . (int)$default['z'];
?>

<section class="map-section">
  <div class="container">
    <h2 class="map-heading">Places To Visit For Events</h2>
    <p class="map-intro">Explore venues in Haarlem and nearby. Use the buttons to jump to a location, or expand the map.</p>

    <div class="map-toolbar">
      <?php foreach ($places as $p): ?>
        <button
          type="button"
          class="map-pill"
          data-ll="<?= h($p['ll']) ?>"
          data-z="<?= (int)$p['z'] ?>"
        >
          <?= h($p['label']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="map-wrapper" id="festivalMapWrap">
      <iframe
        id="festivalMapFrame"
        class="map-iframe"
        src="<?= h($defaultUrl) ?>"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen
      ></iframe>
    </div>

    <div class="map-actions">
      <button type="button" class="map-btn map-btn-primary" id="mapExpandBtn">View it Big</button>
      <a class="map-btn map-btn-secondary" href="<?= h($baseMapUrl) ?>" target="_blank" rel="noopener">Open in Google Maps</a>
    </div>
  </div>
</section>

<!-- Fullscreen overlay -->
<div class="map-overlay" id="mapOverlay" aria-hidden="true">
  <div class="map-overlay-bar">
    <div class="map-overlay-title">Festival Map</div>
    <button type="button" class="map-overlay-close" id="mapCloseBtn" aria-label="Close map">✕</button>
  </div>

  <div class="map-overlay-body">
    <iframe
      id="festivalMapFrameBig"
      class="map-iframe map-iframe-big"
      src="<?= h($defaultUrl) ?>"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      allowfullscreen
    ></iframe>
  </div>
</div>

<script>
(function () {
  const baseUrl = <?= json_encode($baseMapUrl) ?>;

  const frameSmall = document.getElementById('festivalMapFrame');
  const frameBig   = document.getElementById('festivalMapFrameBig');

  const overlay    = document.getElementById('mapOverlay');
  const openBtn    = document.getElementById('mapExpandBtn');
  const closeBtn   = document.getElementById('mapCloseBtn');

  function buildUrl(ll, z) {
    const params = new URLSearchParams();
    params.set('mid', new URL(baseUrl).searchParams.get('mid'));
    params.set('ll', ll);
    params.set('z', String(z));
    return 'https://www.google.com/maps/d/u/0/viewer?' + params.toString();
  }

  function setMap(ll, z) {
    const url = buildUrl(ll, z);
    if (frameSmall) frameSmall.src = url;
    if (frameBig) frameBig.src = url;
  }

  document.querySelectorAll('.map-pill').forEach(btn => {
    btn.addEventListener('click', () => {
      const ll = btn.getAttribute('data-ll');
      const z  = btn.getAttribute('data-z');
      setMap(ll, z);
    });
  });

  openBtn?.addEventListener('click', () => {
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
  });

  closeBtn?.addEventListener('click', () => {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
  });

  // close on backdrop click
  overlay?.addEventListener('click', (e) => {
    if (e.target === overlay) {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }
  });

  // ESC close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }
  });
})();
</script>