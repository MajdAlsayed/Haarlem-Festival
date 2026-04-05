<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$selectedDay = $vm->selectedDay ?? 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($vm->pageTitle ?? 'Haarlem Stories - Events to Explore') ?></title>

    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">

    <style>
        body.stories-dark {
            color: #f0f0f0;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .stories-hero-banner {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            height: 480px;
            overflow: hidden;
        }

        .hero-img {
            background-size: cover;
            background-position: center;
        }

        .hero-overlay {
            position: absolute;
            bottom: 60px;
            left: 60px;
            color: white;
            z-index: 2;
        }

        .hero-overlay h1 { font-size: 3rem; font-weight: 800; margin: 0; }
        .hero-overlay p  { margin-top: 10px; font-size: 1rem; opacity: 0.9; }

        .stories-heading-section {
            background: #d6c39a;
            color: #141414;
            padding: 2.2rem 1rem 2rem;
            border-top: 3px solid #ffcc00;
        }

        .stories-heading-inner { max-width: 1100px; margin: 0 auto; }

        .stories-heading-title {
            font-size: 1.35rem;
            letter-spacing: 0.08em;
            font-weight: 800;
            margin: 0 0 1rem;
            text-transform: uppercase;
        }

        .stories-heading-text {
            max-width: 980px;
            line-height: 1.7;
            margin: 0;
            font-size: 0.98rem;
            color: #1a1a1a;
        }

        .stories-scroll-down {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 1.2rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1a1a1a;
            opacity: 0.9;
        }

        .stories-scroll-icon {
            width: 30px; height: 30px;
            border: 2px solid rgba(0,0,0,0.35);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .stories-venues {
            background: #0a0a1f;
            padding: 2rem 1rem;
            text-align: center;
        }

        .venues-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 1rem; }

        .venue-btn {
            background: #ff9900;
            color: #000;
            padding: 0.6rem 1.5rem;
            border-radius: 999px;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .venue-btn:hover { background: #ffbb44; transform: translateY(-3px); }

        .stories-hero {
            text-align: left;
            padding: 3rem 1rem 1.5rem;
            position: relative;
            max-width: 1100px;
            margin: 0 auto;
        }

        .stories-hero h1 { color: #ffcc00; font-size: 2.6rem; margin: 0 0 0.6rem; }
        .stories-subtitle { color: #ccc; font-size: 1.05rem; max-width: 720px; margin: 0; }

        .info-bubble {
            position: absolute;
            top: 2.5rem; right: 1rem;
            background: #fff; color: #000;
            padding: 1rem 1.4rem;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.6);
            font-size: 0.95rem;
            max-width: 300px;
            line-height: 1.4;
        }
        .info-bubble strong { color: #e00; }

        .day-tabs {
            display: flex;
            justify-content: center;
            gap: 0.8rem;
            flex-wrap: wrap;
            margin: 2rem 0 3rem;
        }

        .day-tab {
            background: #222244;
            color: #ddd;
            padding: 0.75rem 1.5rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }
        .day-tab.active, .day-tab:hover { background: #ff9900; color: #000; }

        .schedule-section {
            padding: 0 1rem 4rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .schedule-row {
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: 1.2rem;
            align-items: center;
            margin: 1.2rem 0;
        }

        .schedule-label {
            color: #ddd;
            font-size: 0.95rem;
            text-align: left;
            opacity: 0.9;
            white-space: nowrap;
        }

        .schedule-content { width: 100%; }
        .day-tabs-inline { justify-content: flex-start; margin: 0; }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 1.2rem;
            align-items: center;
            min-height: 80px;
            justify-content: flex-start;
        }

        .event-tag {
            background: #ffcc00;
            color: #000;
            padding: 0.75rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            text-align: center;
            line-height: 1.4;
        }

        /* ── Story cards ── */
        .stories-cards {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 1rem 4rem;
        }

        .stories-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            align-items: stretch;
        }

        /* Loading state */
        .cards-loading {
            grid-column: 1 / -1;
            text-align: center;
            padding: 48px 20px;
            color: #cfcfcf;
            font-size: 14px;
        }

        .cards-loading-spinner {
            width: 32px; height: 32px;
            border: 3px solid rgba(255,204,0,0.2);
            border-top-color: #ffcc00;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            margin: 0 auto 14px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .stories-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.10);
            background: rgba(255,255,255,0.03);
            box-shadow: 0 10px 28px rgba(0,0,0,0.55);
            display: flex;
            flex-direction: column;
            min-height: 420px;
        }

        .stories-card-img {
            height: 210px;
            background-size: cover;
            background-position: center;
            flex: 0 0 auto;
            filter: saturate(0.95);
        }

        .stories-card-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .stories-card-title {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }

        .stories-card-meta {
            font-size: 12px;
            color: rgba(255,255,255,0.82);
            display: grid;
            gap: 6px;
        }

        .meta-row { display: flex; align-items: center; gap: 8px; }

        .meta-ico {
            width: 18px; height: 18px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,204,0,0.14);
        }

        .stories-card-desc {
            margin: 0;
            font-size: 12.5px;
            line-height: 1.55;
            color: rgba(255,255,255,0.78);
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3.7em;
        }

        .stories-card-actions { display: flex; gap: 10px; margin-top: 8px; }

        .card-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 900;
            font-size: 12px;
            letter-spacing: .06em;
            text-transform: uppercase;
            border-radius: 8px;
            padding: 12px 10px;
            transition: .2s;
            border: 1px solid rgba(255,153,0,0.65);
        }

        .card-btn.primary { background: #ffb400; border-color: #ffb400; color: #000; }
        .card-btn.primary:hover { background: #ffcc55; border-color: #ffcc55; transform: translateY(-1px); }
        .card-btn.outline { background: rgba(0,0,0,0.25); color: #ffb400; }
        .card-btn.outline:hover { background: #ff9900; color: #000; border-color: #ff9900; transform: translateY(-1px); }

        /* Map section */
        .stories-map-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1rem 4rem;
            text-align: center;
        }

        .stories-map-title {
            color: #ffcc00;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin: 0 0 0.3rem;
        }

        .stories-map-subtitle { color: #cfcfcf; margin: 0 0 1.2rem; font-size: 0.95rem; opacity: 0.9; }

        .map-venue-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.8rem;
            margin: 0.8rem 0 1.2rem;
        }

        .map-venue-btn {
            background: #e7e7e7;
            color: #111;
            border: none;
            padding: 0.55rem 1.1rem;
            border-radius: 999px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(0,0,0,0.35);
            transition: 0.2s;
        }
        .map-venue-btn:hover { transform: translateY(-2px); }
        .map-venue-btn.active { background: #ff9900; }

        .stories-map-frame {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto 1.5rem;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid rgba(255,204,0,0.45);
            box-shadow: 0 10px 28px rgba(0,0,0,0.55);
            background: rgba(0,0,0,0.2);
        }

        .stories-map-frame iframe { display: block; width: 100%; height: 520px; border: 0; }

        .btn-map {
            display: inline-block;
            background: #ff9900;
            color: #000;
            padding: 0.85rem 1.4rem;
            border-radius: 10px;
            font-weight: 800;
            text-decoration: none;
            letter-spacing: 0.02em;
            transition: 0.2s;
        }
        .btn-map:hover { background: #ffbb44; transform: translateY(-2px); }

        .stories-breadcrumb {
            background: #0a0a1f;
            padding: 14px 0;
            border-top: 1px solid rgba(255,255,255,0.08);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .stories-breadcrumb-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stories-breadcrumb-link {
            color: #ffffff;
            text-decoration: none;
            text-transform: uppercase;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.04em;
        }
        .stories-breadcrumb-link:hover { color: #ffcc00; }
        .stories-breadcrumb-link.active { color: #ffcc00; }
        .stories-breadcrumb-separator { color: rgba(255,255,255,0.45); font-size: 15px; }

        @media (max-width: 1100px) { .stories-cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 650px)  { .stories-cards-grid { grid-template-columns: 1fr; } }

        @media (max-width: 900px) {
            .event-tag { min-width: 160px; font-size: 0.9rem; padding: 0.65rem 1.2rem; }
            .info-bubble { position: static; margin-top: 1rem; max-width: 100%; }
            .schedule-row { grid-template-columns: 1fr; gap: 0.6rem; align-items: start; }
            .schedule-label { white-space: normal; }
        }

        @media (max-width: 700px) { .stories-map-frame iframe { height: 380px; } }
    </style>
</head>

<body class="stories-dark">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Hero banner -->
    <section class="stories-hero-banner" aria-label="Hero images">
        <?php foreach ($vm->getStoriesHeroImages() as $img): ?>
            <div class="hero-img" style="background-image: url('<?= h($img) ?>');" role="img" aria-label="Stories banner image"></div>
        <?php endforeach; ?>
        <div class="hero-overlay">
            <h1>Welcome to Stories<br>In Haarlem</h1>
            <p>Experience Haarlem Through Stories – Past, Present &amp; Future.</p>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="stories-breadcrumb" aria-label="Breadcrumb">
        <div class="stories-breadcrumb-inner">
            <a href="/" class="stories-breadcrumb-link">HOME</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">→</span>
            <span class="stories-breadcrumb-link active" aria-current="page">STORIES</span>
        </div>
    </nav>

    <!-- Intro section -->
    <section class="stories-heading-section">
        <div class="stories-heading-inner">
            <h2 class="stories-heading-title">The City That Speaks Through Its People</h2>
            <p class="stories-heading-text">
                Haarlem's rich tradition of storytelling lives in every corner of the city — from narrow cobblestone streets
                to centuries-old courtyards. During Stories in Haarlem, local residents, historians, and performers bring
                hidden tales to life through intimate sessions that reveal the city's humor, heart, and heritage.
            </p>
            <div class="stories-scroll-down">
                <span class="stories-scroll-icon" aria-hidden="true">⌄</span>
                <span>Scroll down</span>
            </div>
        </div>
    </section>

    <!-- Venue buttons -->
    <section class="stories-venues" aria-label="Venue navigation">
        <div class="venues-container">
            <a href="#" class="venue-btn">Verhalenhuis Haarlem</a>
            <a href="/stories/venue?slug=de-schuur" class="venue-btn">De Schuur</a>
            <a href="/stories/venue?slug=kweekcafe" class="venue-btn">Kweekcafé</a>
            <a href="#" class="venue-btn">Ten Boom Museum</a>
            <a href="#" class="venue-btn">Elswout Theater</a>
        </div>
    </section>

    <!-- Section header -->
    <div class="stories-hero">
        <h1>Haarlem Stories – Events to Explore</h1>
        <p class="stories-subtitle">
            Discover the best of Haarlem through various storytelling experiences.<br>
            Check age labels for each event.
        </p>
        <div class="info-bubble" role="note">
            <strong>!</strong> Stories are available for different age groups.<br>
            Please check the age label.
        </div>
    </div>

    <!-- Schedule / day filter -->
    <section class="schedule-section" aria-label="Day filter and schedule">

        <div class="schedule-row">
            <div class="schedule-label">Select the Day:</div>
            <div class="schedule-content">
                <div class="day-tabs day-tabs-inline">
                    <?php
                    $days = [
                        'all'      => 'All Events ›',
                        'thursday' => 'Thursday ›',
                        'friday'   => 'Friday ›',
                        'saturday' => 'Saturday ›',
                        'sunday'   => 'Sunday ›',
                    ];
                    foreach ($days as $key => $label): ?>
                        <a class="day-tab <?= $vm->isActive($key) ? 'active' : '' ?>"
                           href="/stories?day=<?= h($key) ?>"
                           aria-current="<?= $vm->isActive($key) ? 'page' : 'false' ?>">
                            <?= h($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedDay !== 'all'): ?>
            <?php
            $nlBlocks  = $vm->schedule['NL'][$selectedDay]  ?? [];
            $engBlocks = $vm->schedule['ENG'][$selectedDay] ?? [];
            ?>

            <div class="schedule-row">
                <div class="schedule-label">Dutch Events:</div>
                <div class="schedule-content">
                    <div class="tags-container">
                        <?php if (empty($nlBlocks)): ?>
                            <span style="color:#cfcfcf;">No Dutch events.</span>
                        <?php else: ?>
                            <?php foreach ($nlBlocks as $b): ?>
                                <span class="event-tag">
                                    <?= h($b['time'] ?? '') ?><br>
                                    <?php if (!empty($b['age'])): ?>Age <?= h($b['age']) ?><?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="schedule-row">
                <div class="schedule-label">English Events:</div>
                <div class="schedule-content">
                    <div class="tags-container">
                        <?php if (empty($engBlocks)): ?>
                            <span style="color:#cfcfcf;">No English events.</span>
                        <?php else: ?>
                            <?php foreach ($engBlocks as $b): ?>
                                <span class="event-tag">
                                    <?= h($b['time'] ?? '') ?><br>
                                    <?php if (!empty($b['age'])): ?>Age <?= h($b['age']) ?><?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </section>

    <!--
        Story cards — loaded via fetch() from /api/stories (Lecture 6).
        The grid starts with a loading spinner; JavaScript replaces it with cards.
        DOMPurify sanitises all API data before writing to innerHTML (Lecture 6 XSS requirement).
    -->
    <section class="stories-cards" aria-label="Story cards">
        <div class="stories-cards-grid" id="storiesGrid">
            <div class="cards-loading" id="cardsLoading">
                <div class="cards-loading-spinner" aria-hidden="true"></div>
                Loading stories…
            </div>
        </div>
    </section>

    <!-- Map section -->
    <section class="stories-map-section" aria-label="Venue map">
        <h2 class="stories-map-title">Places To Visit For Events</h2>
        <p class="stories-map-subtitle">Location: Haarlem, Netherlands</p>

        <div class="map-venue-buttons" id="mapVenueButtons">
            <button type="button" class="map-venue-btn" data-lat="52.40385" data-lng="4.64628">Verhalenhuis Haarlem</button>
            <button type="button" class="map-venue-btn" data-lat="52.3818"  data-lng="4.63931">Schuur</button>
            <button type="button" class="map-venue-btn" data-lat="52.39613" data-lng="4.63569">Kweekcafé</button>
            <button type="button" class="map-venue-btn" data-lat="52.38227" data-lng="4.6354" >Ten Boom Museum</button>
            <button type="button" class="map-venue-btn" data-lat="52.37631" data-lng="4.59906">Elswout Theater</button>
        </div>

        <div class="stories-map-frame">
            <iframe
                id="storiesMap"
                src="https://www.google.com/maps/d/u/0/embed?mid=1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw&ll=52.387877486255384%2C4.630817341343083&z=14"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
                title="Haarlem stories venues map">
            </iframe>
        </div>

        <a class="btn-map"
           href="https://www.google.com/maps/d/u/0/viewer?mid=1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw&ll=52.387877486255384%2C4.630817341343083&z=14"
           target="_blank" rel="noopener noreferrer">
            View Live Map ›
        </a>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<!--
    DOMPurify — sanitises API data before writing to innerHTML.
    Lecture 6 requirement: "use a library like DOMPurify to handle sanitization".
-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.5/purify.min.js"></script>

<script>
(function () {

    /* ── 1. Story cards via fetch() (Lecture 6) ── */

    const grid    = document.getElementById('storiesGrid');
    const loading = document.getElementById('cardsLoading');

    // Read the current day filter from PHP so the API call matches the page
    const currentDay = <?= json_encode($selectedDay) ?>;

    const apiUrl = '/api/stories' + (currentDay !== 'all' ? '?day=' + encodeURIComponent(currentDay) : '');

    /**
     * Build one card's HTML string from an API story object.
     * Every value is passed through DOMPurify.sanitize() before use in innerHTML.
     */
    function buildCard(s) {
        const img      = DOMPurify.sanitize(s.image_path  || '/images/Stories/cards/default.jpg');
        const name     = DOMPurify.sanitize(s.name        || 'Story');
        const day      = DOMPurify.sanitize(s.event_day   ? s.event_day.charAt(0).toUpperCase() + s.event_day.slice(1) : '');
        const time     = DOMPurify.sanitize(s.start_time  || '');
        const venue    = DOMPurify.sanitize(s.venue_name  || '');
        const city     = DOMPurify.sanitize(s.venue_city  || '');
        const lang     = DOMPurify.sanitize(s.language    || '');
        const age      = DOMPurify.sanitize(s.age         || '');
        const desc     = DOMPurify.sanitize(s.description || '');
        const storyId  = parseInt(s.story_id, 10) || 0;

        const venueLine = venue + (city ? ', ' + city : '');

        const langRow = lang
            ? `<div class="meta-row"><span class="meta-ico">🌐</span><span>Lang: ${lang}</span></div>`
            : '';

        const ageRow = age
            ? `<div class="meta-row"><span class="meta-ico">🔞</span><span>Age ${age}</span></div>`
            : '';

        return `
            <article class="stories-card">
                <div class="stories-card-img"
                     style="background-image:url('${img}')"
                     role="img"
                     aria-label="${name}"></div>
                <div class="stories-card-body">
                    <h3 class="stories-card-title">${name}</h3>
                    <div class="stories-card-meta">
                        <div class="meta-row">
                            <span class="meta-ico">📅</span>
                            <span>${day} ${time}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-ico">📍</span>
                            <span>${venueLine}</span>
                        </div>
                        ${langRow}
                        ${ageRow}
                    </div>
                    <p class="stories-card-desc">${desc}</p>
                    <div class="stories-card-actions">
                        <a class="card-btn primary" href="/tickets">BUY TICKETS</a>
                        <a class="card-btn outline" href="/stories/detail?id=${storyId}">MORE INFO</a>
                    </div>
                </div>
            </article>`;
    }

    // Fetch stories from the JSON API
    fetch(apiUrl)
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            loading.remove();

            if (!data.stories || data.stories.length === 0) {
                grid.innerHTML = '<p style="color:#cfcfcf;padding:20px;">No stories found.</p>';
                return;
            }

            // Build all cards and inject in one operation
            grid.innerHTML = data.stories.map(buildCard).join('');
        })
        .catch(function (err) {
            console.error('Failed to load stories:', err);
            loading.innerHTML = '<p style="color:#cfcfcf;">Could not load stories. Please refresh the page.</p>';
        });

    /* ── 2. Map venue buttons (Lecture 5: addEventListener, no inline onclick) ── */

    const mapFrame   = document.getElementById('storiesMap');
    const mapButtons = document.getElementById('mapVenueButtons');
    const MAP_MID    = '1Y0K04QlhJ-dwhjVOe-8iT3bFJQ5yPAw';
    const MAP_ZOOM   = 16;

    if (mapFrame && mapButtons) {
        mapButtons.addEventListener('click', function (e) {
            const btn = e.target.closest('.map-venue-btn');
            if (!btn) return;

            const lat = btn.dataset.lat;
            const lng = btn.dataset.lng;
            if (!lat || !lng) return;

            mapFrame.src = 'https://www.google.com/maps/d/u/0/embed?mid='
                + MAP_MID
                + '&ll=' + encodeURIComponent(lat + ',' + lng)
                + '&z='  + MAP_ZOOM;

            mapFrame.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Update active state
            mapButtons.querySelectorAll('.map-venue-btn').forEach(function (b) {
                b.classList.remove('active');
            });
            btn.classList.add('active');
        });
    }

})();
</script>

</body>
</html>