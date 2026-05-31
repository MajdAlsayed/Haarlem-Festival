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
    <title><?= h($vm->pageTitle ?? 'Events - Haarlem Stories') ?></title>

    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/Stories/events.css?v=<?= h($app['css_version'] ?? '1') ?>">
</head>

<body class="stories-events-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Breadcrumb -->
    <nav class="stories-breadcrumb" aria-label="Breadcrumb">
        <div class="stories-breadcrumb-inner">
            <a href="/" class="stories-breadcrumb-link">HOME</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">→</span>
            <a href="/stories" class="stories-breadcrumb-link">STORIES</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">→</span>
            <span class="stories-breadcrumb-link active" aria-current="page">EVENTS</span>
        </div>
    </nav>

    <!-- Section header -->
    <div class="stories-hero">
        <h1>Events of Haarlem Stories</h1>
        <p class="stories-subtitle">
            Discover the best of Haarlem through various storytelling experiences.<br>
            Check age labels for each event.
        </p>
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
                           href="/stories/events?day=<?= h($key) ?>"
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
        Story cards — loaded via fetch() from /api/stories.
        The grid starts with a loading spinner; JavaScript replaces it with cards.
        DOMPurify sanitises all API data before writing to innerHTML.
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
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.9/dist/purify.min.js"></script>
<script src="/js/Stories/cards.js"></script>
</body>
</html>
