<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$selectedDay = $vm->selectedDay ?? 'all';

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

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Hero -->
    <?php require __DIR__ . '/../partials/page-hero.php'; ?>

    <!-- Breadcrumb -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <!-- Schedule / day filter -->
    <section class="container schedule-section" aria-label="Day filter and schedule">

        <div class="filter-tabs stories-day-tabs" aria-label="Stories day filter">
            <?php
            $days = [
                    'all'      => 'All Events ›',
                    'thursday' => 'Thursday ›',
                    'friday'   => 'Friday ›',
                    'saturday' => 'Saturday ›',
                    'sunday'   => 'Sunday ›',
            ];
            foreach ($days as $key => $label): ?>
                <a class="filter-tab stories-day-tab <?= $vm->isActive($key) ? 'active' : '' ?>"
                   href="/stories/events?day=<?= h($key) ?>"
                   aria-current="<?= $vm->isActive($key) ? 'page' : 'false' ?>">
                    <?= h($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($selectedDay !== 'all'): ?>
            <?php
            $nlBlocks  = $vm->schedule['NL'][$selectedDay]  ?? [];
            $engBlocks = $vm->schedule['ENG'][$selectedDay] ?? [];
            ?>

            <div class="schedule-row">
                <div class="section-subtitle stories-schedule-label">Dutch Events:</div>
                <div class="schedule-content">
                    <div class="tags-container">
                        <?php if (empty($nlBlocks)): ?>
                            <span class="copy-text copy-text--sm copy-text--muted">No Dutch events.</span>
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
                <div class="section-subtitle stories-schedule-label">English Events:</div>
                <div class="schedule-content">
                    <div class="tags-container">
                        <?php if (empty($engBlocks)): ?>
                            <span class="copy-text copy-text--sm copy-text--muted">No English events.</span>
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
    <section class="container stories-cards" aria-label="Story cards">
        <div class="stories-cards-grid" id="storiesGrid">
            <div class="cards-loading copy-text copy-text--muted" id="cardsLoading">
                <div class="cards-loading-spinner" aria-hidden="true"></div>
                Loading stories…
            </div>
        </div>
    </section>

    <!-- Map section -->
    <?php require __DIR__ . '/partials/stories-map.php'; ?>

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
