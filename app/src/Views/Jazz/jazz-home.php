<?php
/**
 * Jazz landing page (/jazz) — the big grid of shows with day filters.
 *
 * JazzController::index() hands us events + config; we group by weekday, sort Thursday/Friday the way CMS prefers,
 * and build “All events” so repeat acts don’t spam the list. Hearts use localStorage for a lightweight “my program”.
 * We load Bootstrap + our stylesheet here ($skipHeaderStyleSheet) so this page matches the rest of the site header.
 */
/** @var \App\ViewModels\JazzViewModel $vm */
/** @var array<string, mixed> $jazzConfig */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$heroFile = (string) ($jazzConfig['hero_image'] ?? 'hero-jazz.jpg');
$hero = '/images/jazz/' . rawurlencode($heroFile);

$eventCardImages = $vm->eventCardImages;
$thursdayOrder = $jazzConfig['thursday_order'] ?? [];
$fridayOrder = $jazzConfig['friday_order'] ?? [];
$saturdayOrder = $jazzConfig['saturday_order'] ?? [];
$sundayOrder = $jazzConfig['sunday_order'] ?? [];

// Split the flat list into buckets — every show keeps its own card even if the same band plays twice
$byDay = ['thursday' => [], 'friday' => [], 'saturday' => [], 'sunday' => []];
$allEvents = is_array($vm->events) ? $vm->events : [];
foreach ($allEvents as $e) {
    $day = strtolower(trim((string)($e['event_day'] ?? 'friday')));
    if (isset($byDay[$day])) {
        $byDay[$day][] = $e;
    }
}

// CMS can pin headline acts: if a title is in thursday_order / friday_order / …, respect that order, else fall back to clock time
$sortByOrder = function (array $order) {
    return function ($a, $b) use ($order) {
        $posA = array_search((string)$a['title'], $order, true);
        $posB = array_search((string)$b['title'], $order, true);
        if ($posA !== false && $posB !== false) return $posA <=> $posB;
        if ($posA !== false) return -1;
        if ($posB !== false) return 1;
        return strcmp($a['start_time'] ?? '', $b['start_time'] ?? '');
    };
};
usort($byDay['thursday'], $sortByOrder($thursdayOrder));
usort($byDay['friday'], $sortByOrder($fridayOrder));
usort($byDay['saturday'], $sortByOrder($saturdayOrder));
usort($byDay['sunday'], $sortByOrder($sundayOrder));

$events = array_merge($byDay['thursday'], $byDay['friday'], $byDay['saturday'], $byDay['sunday']);

// “All events” tab: these names only get one card so the grid doesn’t look like copy-paste when they play multiple slots
$allEventsDedupTitles = ['Wicked Jazz Sounds', 'Evolve', 'The Nordanians', 'Gumbo Kings', 'Gare du Nord'];
$seenForAll = [];
$allEventsDuplicateKeys = [];
foreach ($events as $i => $e) {
    $title = trim((string)($e['title'] ?? ''));
    $titleKey = $title; // keep DB spelling for the duplicate check
    $isInDedupList = false;
    foreach ($allEventsDedupTitles as $dedupTitle) {
        if (strcasecmp($title, $dedupTitle) === 0) {
            $isInDedupList = true;
            $titleKey = $dedupTitle;
            break;
        }
    }
    if ($isInDedupList) {
        if (isset($seenForAll[$titleKey])) {
            $allEventsDuplicateKeys[$i] = true;
        } else {
            $seenForAll[$titleKey] = true;
        }
    }
}

$slugify = function (string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
    return trim($s, '-');
};

$allowedDetails = [];
$artistPages = is_array($jazzConfig['artist_pages'] ?? null) ? $jazzConfig['artist_pages'] : [];
// Map “slug from URL” → artist detail link so card buttons know where to send people
foreach ($artistPages as $pageSlug => $meta) {
    if (!is_array($meta)) {
        continue;
    }
    $pageSlug = preg_replace('/[^a-z0-9\-]/', '', (string) $pageSlug) ?: (string) $pageSlug;
    $artistTitle = trim((string) ($meta['title'] ?? ''));
    if ($artistTitle === '' || $pageSlug === '') {
        continue;
    }
    $allowedDetails[$slugify($artistTitle)] = '/jazz/' . $pageSlug;
}

// Settings for the page title, styles, body class
$pageTitle = $vm->pageTitle;
$pageStyles = ['/css/pages/jazz.css'];
$bodyClass = 'jazz-page jazz-filter-all';

// Hero settings
$heroModifier = 'festival-hero--jazz';
$heroImage = $hero;
$heroImageAlt = 'The Jazz Lounge';
$heroTitle = 'The Jazz Lounge';
$heroSubtitle = 'The heartbeat of the historic square.';
$heroButtonText = '';
$heroButtonUrl = '';
$heroButtonClass = 'btn btn--light';

// Breadcrumbs
$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Jazz', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero -->
    <?php require __DIR__ . '/../partials/festival-hero.php'; ?>

    <!-- Breadcrumbs nav -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <!-- Intro + filter + grid -->
    <section class="container jazz-intro">

        <p class="section-lead section-lead--center jazz-lead">
            Find your rhythm in the heart of the city. Haarlem Jazz Festival is where timeless melodies meet
            modern grooves. Join the celebration, feel the beat, and experience the true spirit of jazz.
        </p>

        <div class="filter-tabs jazz-filterbar" role="tablist" aria-label="Jazz day filter">
            <button class="filter-tab jazz-filterbtn is-active" type="button" data-jazz-filter="all" aria-pressed="true">All Events</button>
            <button class="filter-tab jazz-filterbtn" type="button" data-jazz-filter="thursday" aria-pressed="false">Thursday</button>
            <button class="filter-tab jazz-filterbtn" type="button" data-jazz-filter="friday" aria-pressed="false">Friday</button>
            <button class="filter-tab jazz-filterbtn" type="button" data-jazz-filter="saturday" aria-pressed="false">Saturday</button>
            <button class="filter-tab jazz-filterbtn" type="button" data-jazz-filter="sunday" aria-pressed="false">Sunday</button>
        </div>

        <div class="jazz-events-grid" id="jazzGrid">
            <?php foreach ($events as $i => $e):
                $day = $e['event_day'] ?? 'friday';
                $title = (string)$e['title'];
                $slug = $slugify($title);

                $start = $e['start_time'] ?: '00:00';
                $end = $e['end_time'] ?: '';
                $startDot = str_replace(':', '.', $start);
                $endDot = $end ? str_replace(':', '.', $end) : '';
                $timeRange = $endDot ? "{$startDot} - {$endDot}" : $startDot;

                $venue = $e['venue_name'] ?? '';
                $hall = $e['hall'] ?? null;
                $hallLabel = $hall ?: $venue;
                $timeCompact = str_replace(':', '.', $start ?? '00.00');
                $whenWhereCompact = trim($hallLabel) . ' ' . $timeCompact;
                $place = trim($venue . ($hall ? " ({$hall})" : ''));
                $placeDisplay = $place !== '' ? 'the ' . $place : '';

                $price = $e['price'] !== null ? (float)$e['price'] : null;

                // Prefer the filename from jazz config; otherwise guess from the URL-friendly slug
                $imgFile = $eventCardImages[$title] ?? ($slug . '.png');
                $img = '/images/jazz/' . rawurlencode($imgFile);

                $detailsUrl = $allowedDetails[$slug] ?? null;
                $isBookable = $detailsUrl !== null;
                $isAllEventsDuplicate = isset($allEventsDuplicateKeys[$i]);
            ?>
            <article class="festival-card festival-card--jazz jazz-event-card<?= $isAllEventsDuplicate ? ' jazz-all-events-duplicate' : '' ?>" data-day="<?= $h($day) ?>" data-event-id="<?= (int)$e['event_id'] ?>">
                <div class="jazz-event-media">
                    <img src="<?= $h($img) ?>"
                         alt="<?= $h($title) ?>">

                    <button type="button"
                            class="jazz-heart-btn jazz-card-all-only"
                            data-save-event="<?= (int)$e['event_id'] ?>"
                            aria-label="Save to program">♥</button>
                </div>

                <div class="jazz-event-body">
                    <h3 class="jazz-event-title"><?= $h($title) ?></h3>

                    <!-- All Events: compact line only -->
                    <p class="copy-text copy-text--sm jazz-event-whenwhere jazz-event-whenwhere-compact jazz-card-compact-only">
                        <?= $h($whenWhereCompact) ?>
                    </p>

                    <!-- Day filter: full time + venue -->
                    <p class="copy-text copy-text--sm  jazz-event-whenwhere jazz-card-day-only">
                        (<?= $h($timeRange) ?>) at <?= $h($placeDisplay) ?>
                    </p>

                    <!-- Day filter: description + price -->
                    <p class="copy-text copy-text--sm  jazz-event-desc jazz-card-day-only">
                        <?= $h($e['description'] ?? '') ?>
                        <?php if ($price !== null && $price > 0): ?>
                            <br> - Ticket price: <?= $h(number_format($price, 2)) ?>€
                        <?php else: ?>
                            <br> - Free for all visitors. No reservation needed.
                        <?php endif; ?>
                    </p>

                    <!-- Day view (Thu–Sun): Artist details + Save to program use same primary style -->
                    <div class="jazz-event-actions">
                        <?php if ($detailsUrl): ?>
                            <a class="btn btn--light btn--sm jazz-event-details-btn" href="<?= $h($detailsUrl) ?>">Artist details</a>
                        <?php else: ?>
                            <button class="btn btn--light btn--sm jazz-event-details-btn" type="button" disabled aria-disabled="true">Artist details</button>
                        <?php endif; ?>

                        <!-- If you can save event to the program/cart -->
                        <?php if ($isBookable): ?>
                            <button
                                    class="btn btn--primary btn--sm jazz-card-day-only"
                                    type="button"
                                    data-save-event="<?= (int)$e['event_id'] ?>"
                            >
                                Save to program
                            </button>
                        <?php else: ?>
                            <button
                                    class="btn btn--primary btn--sm jazz-card-day-only"
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                            >
                                Save to program
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<!-- Jazz filter + local "save to program" (localStorage) -->
<script>
(function () {
    var buttons = document.querySelectorAll('.jazz-filterbtn');
    var cards = document.querySelectorAll('.jazz-event-card');

    function setActive(btn) {
        buttons.forEach(function (b) {
            b.classList.remove('is-active');
            b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('is-active');
        btn.setAttribute('aria-pressed', 'true');
    }

    function applyFilter(filter) {
        var isAll = (filter === 'all');
        document.body.classList.toggle('jazz-filter-all', isAll);
        document.body.classList.toggle('jazz-filter-day', !isAll);
        cards.forEach(function (c) {
            var day = (c.getAttribute('data-day') || 'friday').toLowerCase();
            var show = isAll || (day === filter);
            c.classList.toggle('is-hidden', !show);
            // In All Events, hide duplicate cards (2nd+ per artist)
            if (isAll && c.classList.contains('jazz-all-events-duplicate')) {
                c.style.setProperty('display', 'none', 'important');
            } else if (!isAll && c.classList.contains('jazz-all-events-duplicate')) {
                c.style.removeProperty('display');
            }
        });
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var filter = (btn.getAttribute('data-jazz-filter') || 'all').toLowerCase();
            setActive(btn);
            applyFilter(filter);
        });
    });

    applyFilter('all');

    var STORAGE_KEY = 'jazz_program_event_ids';

    function loadSaved() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            var arr = raw ? JSON.parse(raw) : [];
            return Array.isArray(arr) ? arr : [];
        } catch (e) { return []; }
    }

    function saveSaved(arr) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
    }

    function updateBadge(saved) {
        var badge = document.querySelector('.cart-badge');
        if (badge) badge.textContent = String(saved.length);
    }

    function sync(saved) {
        document.querySelectorAll('[data-save-event]').forEach(function (btn) {
            var id = parseInt(btn.getAttribute('data-save-event') || '0', 10);
            if (!id) return;
            btn.classList.toggle('is-saved', saved.indexOf(id) !== -1);
        });
    }

    function toggle(id) {
        var saved = loadSaved();
        var idx = saved.indexOf(id);
        if (idx === -1) saved.push(id); else saved.splice(idx, 1);
        saveSaved(saved);
        updateBadge(saved);
        sync(saved);
    }

    var initial = loadSaved();
    updateBadge(initial);
    sync(initial);

    document.querySelectorAll('[data-save-event]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var id = parseInt(btn.getAttribute('data-save-event') || '0', 10);
            if (id) toggle(id);
        });
    });
})();
</script>

</body>
</html>