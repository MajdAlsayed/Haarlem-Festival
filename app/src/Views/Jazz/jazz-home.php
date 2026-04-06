<?php
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

// One card per event (so each day shows all its events; same artist can appear on multiple days)
$byDay = ['thursday' => [], 'friday' => [], 'saturday' => [], 'sunday' => []];
$allEvents = is_array($vm->events) ? $vm->events : [];
foreach ($allEvents as $e) {
    $day = strtolower(trim((string)($e['event_day'] ?? 'friday')));
    if (isset($byDay[$day])) {
        $byDay[$day][] = $e;
    }
}

// Sort Thursday by thursday_order, Friday by friday_order, then start_time; other days by start_time
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

// For All Events: show only one card per artist (hide 2nd+ occurrence) for these titles
$allEventsDedupTitles = ['Wicked Jazz Sounds', 'Evolve', 'The Nordanians', 'Gumbo Kings', 'Gare du Nord'];
$seenForAll = [];
$allEventsDuplicateKeys = [];
foreach ($events as $i => $e) {
    $title = trim((string)($e['title'] ?? ''));
    $titleKey = $title; // match exactly from DB
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $h($vm->pageTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version'] ?? '1') ?>&jazz=4">
</head>
<body class="jazz-page jazz-filter-all">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero -->
    <section class="jazz-hero" style="background-image: linear-gradient(120deg, rgba(0,0,0,0.45), rgba(0,0,0,0.80)), url('<?= $h($hero) ?>');">
        <div class="jazz-hero-content container">
            <h1 class="jazz-hero-title">The Jazz Lounge</h1>
            <p class="jazz-hero-subtitle">The heartbeat of the historic square.</p>
        </div>
    </section>

    <!-- Intro + filter + grid -->
    <section class="container jazz-intro">
        <nav class="breadcrumbs jazz-breadcrumbs">
            <a href="/">Festival</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-current">Jazz</span>
        </nav>

        <p class="jazz-lead">
            Find your rhythm in the heart of the city. Haarlem Jazz Festival is where timeless melodies meet
            modern grooves. Join the celebration, feel the beat, and experience the true spirit of jazz.
        </p>

        <div class="jazz-filterbar" role="tablist" aria-label="Jazz day filter">
            <button class="jazz-filterbtn is-active" type="button" data-jazz-filter="all" aria-pressed="true">All Events</button>
            <button class="jazz-filterbtn" type="button" data-jazz-filter="thursday" aria-pressed="false">Thursday</button>
            <button class="jazz-filterbtn" type="button" data-jazz-filter="friday" aria-pressed="false">Friday</button>
            <button class="jazz-filterbtn" type="button" data-jazz-filter="saturday" aria-pressed="false">Saturday</button>
            <button class="jazz-filterbtn" type="button" data-jazz-filter="sunday" aria-pressed="false">Sunday</button>
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

                // Use prototype card image if mapped, else try slug.png then slug.jpg
                $imgFile = $eventCardImages[$title] ?? ($slug . '.png');
                $img = '/images/jazz/' . rawurlencode($imgFile);

                $detailsUrl = $allowedDetails[$slug] ?? null;
                $isAllEventsDuplicate = isset($allEventsDuplicateKeys[$i]);
            ?>
            <article class="jazz-event-card<?= $isAllEventsDuplicate ? ' jazz-all-events-duplicate' : '' ?>" data-day="<?= $h($day) ?>" data-event-id="<?= (int)$e['event_id'] ?>">
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
                    <p class="jazz-event-whenwhere jazz-event-whenwhere-compact jazz-card-compact-only">
                        <?= $h($whenWhereCompact) ?>
                    </p>

                    <!-- Day filter: full time + venue -->
                    <p class="jazz-event-whenwhere jazz-card-day-only">
                        (<?= $h($timeRange) ?>) at <?= $h($placeDisplay) ?>
                    </p>

                    <!-- Day filter: description + price -->
                    <p class="jazz-event-desc jazz-card-day-only">
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
                            <a class="jazz-btn jazz-btn-primary" href="<?= $h($detailsUrl) ?>">Artist details</a>
                        <?php else: ?>
                            <button class="jazz-btn jazz-btn-primary" type="button" disabled aria-disabled="true">Artist details</button>
                        <?php endif; ?>
                        <button class="jazz-btn jazz-btn-primary jazz-card-day-only" type="button" data-save-event="<?= (int)$e['event_id'] ?>">Save to program</button>
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