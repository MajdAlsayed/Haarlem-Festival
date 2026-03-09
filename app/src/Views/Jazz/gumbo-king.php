<?php
/** @var \App\ViewModels\JazzArtistViewModel $viewModel */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

/** @var array<int, array<string,mixed>> $events */
$events = $viewModel->events;

// Day to display date (e.g. Friday 26, May)
$dayToLabel = [
    'thursday' => 'Thursday 25, July',
    'friday'   => 'Friday 26, July',
    'saturday' => 'Saturday 27, July',
    'sunday'   => 'Sunday 28, July',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $h($viewModel->artistTitle) ?> — Jazz</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version']) ?>">
</head>
<body class="jazz-page jazz-artist-page jazz-gumbo-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- Hero: title and tagline left-aligned -->
    <section class="jazz-artist-hero jazz-gumbo-hero" style="background-image: linear-gradient(120deg, rgba(0,0,0,0.55), rgba(0,0,0,0.80)), url('<?= $h($viewModel->heroImage) ?>');">
        <div class="container jazz-artist-hero-content jazz-gumbo-hero-content">
            <h1>Gumbo kings</h1>
            <p class="jazz-artist-tagline"><?= $h($viewModel->tagline) ?></p>
        </div>
    </section>

    <section class="container jazz-artist-body">
        <nav class="breadcrumbs jazz-breadcrumbs">
            <a href="/">Festival</a>
            <span class="breadcrumb-sep">›</span>
            <a href="/jazz">Jazz</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-current"><?= $h($viewModel->artistTitle) ?></span>
        </nav>

        <!-- Gumbo Kings introduction (prototype: left-aligned heading, justified paragraph) -->
        <article class="jazz-gumbo-intro">
            <h2 class="jazz-section-title">Gumbo Kings</h2>
            <p class="jazz-artist-bio jazz-gumbo-bio-justified">
                Gumbo Kings is a dynamic five-piece band from the Netherlands, known for their modern yet soulful interpretation of Rhythm 'n Blues and New Orleans Groove. Inspired by legends of Memphis Soul and Delta Blues, they deliver an energetic mix of raw sound, smooth melodies, and timeless musical vibes. With their powerful stage presence and engaging performances, the Gumbo Kings have become a festival favorite, breathing life into jazz traditions with a contemporary twist.
            </p>
        </article>

        <!-- Plan Your Gumbo Kings Experience -->
        <section class="jazz-plan-section jazz-gumbo-plan">
            <h3 class="jazz-section-subtitle">Plan Your Gumbo Kings Experience</h3>

            <div class="jazz-gumbo-cards">
                <div class="jazz-gumbo-card">
                    <div class="jazz-gumbo-card-media jazz-gumbo-video-thumb">
                        <span class="jazz-gumbo-play" aria-hidden="true">▶</span>
                    </div>
                    <p class="jazz-gumbo-card-desc">A Night with Gumbo Kings - Discover the vibrant energy of Gumbo Kings' live performance and their signature New Orleans sound.</p>
                    <a href="#" class="jazz-btn jazz-btn-primary jazz-gumbo-card-btn">View All Tour Dates</a>
                </div>
                <div class="jazz-gumbo-card">
                    <div class="jazz-gumbo-card-media">
                        <img src="<?= $h($viewModel->heroImage) ?>" alt="Gumbo Kings album" class="jazz-gumbo-album-img">
                    </div>
                    <p class="jazz-gumbo-card-desc">In The Dark - Immerse yourself in Gumbo Kings' critically acclaimed album, blending soul, blues, and groove.</p>
                    <a href="#" class="jazz-btn jazz-btn-primary jazz-gumbo-card-btn">Listen Now</a>
                </div>
            </div>

            <div class="jazz-gumbo-schedule-wrap">
                <p class="jazz-gumbo-plan-visit"><span class="jazz-gumbo-cal-icon" aria-hidden="true">📅</span> Plan Your Visit</p>
                <div class="jazz-schedule">
                    <table class="jazz-table jazz-gumbo-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Tickets</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="6" class="jazz-muted">No schedule rows found for this artist.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $e): ?>
                                    <?php
                                    $d = strtolower($e['event_day'] ?? 'friday');
                                    $dateLabel = $dayToLabel[$d] ?? ucfirst($d);
                                    $s = $e['start_time'] ?? ($app['default_event_time'] ?? '18:00');
                                    $en = $e['end_time'] ?? null;
                                    $tr = $en ? "{$s} - {$en}" : $s;
                                    $loc = $e['venue_name'] ?? '';
                                    $p = isset($e['price']) ? (float)$e['price'] : 0;
                                    $seats = $e['seats'] ?? null;
                                    ?>
                                    <tr>
                                        <td><?= $h($dateLabel) ?></td>
                                        <td><?= $h($tr) ?></td>
                                        <td><?= $h($loc) ?></td>
                                        <td><?= $p > 0 ? '€ ' . $h(number_format($p, 2)) : 'Free' ?></td>
                                        <td><?= $seats !== null ? $h($seats) : '—' ?></td>
                                        <td>
                                            <button type="button" class="jazz-btn jazz-btn-primary jazz-gumbo-add-btn" data-save-event="<?= (int)$e['event_id'] ?>">Add to Program</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="jazz-gumbo-schedule-note">All tickets are subject to availability.</p>
            </div>
        </section>

        <!-- Career Highlights (prototype: orange years, left-aligned) -->
        <section class="jazz-highlights jazz-gumbo-highlights">
            <h3 class="jazz-section-subtitle">Career Highlights</h3>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2019</strong> — Earned the title of "most booked band" at Popronde, the Netherlands' leading traveling music festival. This distinction highlighted their growing momentum and broad appeal to audiences in cities across the country.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2020</strong> — Delivered a milestone performance on the prestigious NPO Soul & Jazz stage at Noorderslag. This appearance at one of the biggest Dutch showcase events cemented their status as a standout talent within the jazz and soul community.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2021</strong> — Released the acclaimed single "Hurtin'," a track that continued their signature fusion of modern Rhythm 'n Blues with the vintage sounds of New Orleans and Memphis.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2022</strong> — Unveiled their debut album, In The Dark, produced by Paul Willemsen. The record was praised for its authentic yet modern vibe, featuring a dynamic range of soulful ballads and high-octane tracks.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">Live Performances</strong> — Renowned for their high-voltage stage energy, the Gumbo Kings have thrilled crowds at iconic venues such as Paradiso Amsterdam and Luxor Live, as well as at various international jazz festivals.</p>
        </section>

        <!-- Band Members (prototype: striped background, centered title & text, images by name) -->
        <?php
        $memberImages = [
            'Boy Vielvoije' => 'Boy veilvoije.png',
            'Marc Jansen'   => 'Marc jansen.png',
            'Thomas Hanenburg' => 'Thomas Hanenburg.png',
            'Jonne Venmans' => 'Jonne venmans.png',
            'Remon Hubert'  => 'Remon Hubert.png',
        ];
        $members = [
            ['name' => 'Boy Vielvoije', 'role' => 'Vocals and harmonica'],
            ['name' => 'Marc Jansen', 'role' => 'Guitar and vocals'],
            ['name' => 'Thomas Hanenburg', 'role' => 'Keyboards'],
            ['name' => 'Jonne Venmans', 'role' => 'Bass guitar'],
            ['name' => 'Remon Hubert', 'role' => 'Drums'],
        ];
        ?>
        <section class="jazz-band jazz-gumbo-band">
            <h3 class="jazz-section-subtitle jazz-gumbo-band-title">Band Members</h3>
            <div class="jazz-gumbo-members">
                <?php foreach ($members as $m):
                    $imgFile = $memberImages[$m['name']] ?? null;
                    $imgSrc = $imgFile ? '/images/jazz/' . rawurlencode($imgFile) : $viewModel->heroImage;
                ?>
                    <article class="jazz-gumbo-member">
                        <div class="jazz-gumbo-member-img">
                            <img src="<?= $h($imgSrc) ?>" alt="<?= $h($m['name']) ?>">
                        </div>
                        <div class="jazz-gumbo-member-info">
                            <strong class="jazz-gumbo-member-name"><?= $h($m['name']) ?></strong>
                            <span class="jazz-gumbo-member-role"><?= $h($m['role']) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="jazz-back">
            <a class="jazz-back-btn" href="/jazz">‹ Back to Jazz</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
    var STORAGE_KEY = 'jazz_program_event_ids';
    function loadSaved() { try { var raw = localStorage.getItem(STORAGE_KEY); var arr = raw ? JSON.parse(raw) : []; return Array.isArray(arr) ? arr : []; } catch (e) { return []; } }
    function saveSaved(arr) { localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); }
    function updateBadge(arr) { var b = document.querySelector('.cart-badge'); if (b) b.textContent = String(arr.length); }
    document.querySelectorAll('.jazz-gumbo-add-btn[data-save-event]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = parseInt(btn.getAttribute('data-save-event') || '0', 10);
            if (!id) return;
            var saved = loadSaved();
            if (saved.indexOf(id) === -1) saved.push(id); else saved.splice(saved.indexOf(id), 1);
            saveSaved(saved);
            updateBadge(saved);
        });
    });
})();
</script>
</body>
</html>
