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
    <link rel="stylesheet" href="/css/Stories/home.css?v=<?= h($app['css_version'] ?? '1') ?>">
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
        const lang     = DOMPurify.sanitize(s.language    || '');
        const age      = DOMPurify.sanitize(s.age         || '');
        const desc     = DOMPurify.sanitize(s.description || '');
        const storyId  = parseInt(s.story_id, 10) || 0;

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

})();
</script>

</body>
</html>
