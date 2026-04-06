<?php
/** @var \App\ViewModels\JazzArtistViewModel $viewModel */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$events = $viewModel->events;
$discography = $viewModel->discography;

$discographyForJs = [];
foreach ($discography as $t) {
    $discographyForJs[] = [
        'title' => (string) ($t['title'] ?? ''),
        'releaseYear' => $t['release_year'] ?? null,
        'durationSeconds' => $t['duration_seconds'] ?? null,
        'playCount' => (int) ($t['play_count'] ?? 0),
        'imageUrl' => (string) ($t['image_url'] ?? ''),
        'audioUrl' => (string) ($t['audio_url'] ?? ''),
    ];
}
$discographyJson = json_encode(
    $discographyForJs,
    JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);

// Video card line: match prototype when we have a schedule row
$firstEvent = $events[0] ?? null;
if ($firstEvent !== null) {
    $vd = strtolower((string) ($firstEvent['event_day'] ?? 'friday'));
    $vs = (string) ($firstEvent['start_time'] ?? ($app['default_event_time'] ?? '18:00'));
    $ven = $firstEvent['end_time'] ?? null;
    $vtime = $ven !== null && $ven !== '' ? "{$vs} - {$ven}" : $vs;
    $vtimeDisplay = str_replace(':', '.', $vtime);
    $vlocRaw = trim((string) ($firstEvent['venue_name'] ?? 'Patronaat'));
    $vlocTitle = $vlocRaw !== '' ? ucfirst(strtolower($vlocRaw)) : 'Patronaat';
    $vhallRaw = trim((string) ($firstEvent['hall'] ?? ''));
    $vhallTitle = $vhallRaw !== '' ? ucfirst(strtolower($vhallRaw)) : 'Main hall';
    $karsuVideoMeta = ucfirst($vd) . ' - (' . $vtimeDisplay . ') at the ' . $vlocTitle . ' (' . $vhallTitle . ')';
    $karsuVideoPrice = isset($firstEvent['price']) ? (float) $firstEvent['price'] : 23.0;
} else {
    $karsuVideoMeta = 'Date: TBD | Time: TBD | Patronaat, Main hall';
    $karsuVideoPrice = 23.0;
}

$cardPreviewTrack = $discography[0] ?? null;
$cardPreviewUrl = is_array($cardPreviewTrack) && ($cardPreviewTrack['audio_url'] ?? '') !== ''
    ? (string) $cardPreviewTrack['audio_url']
    : '';
$cardPreviewTitle = is_array($cardPreviewTrack) && ($cardPreviewTrack['title'] ?? '') !== ''
    ? (string) $cardPreviewTrack['title']
    : 'Preview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $h($viewModel->artistTitle) ?> — Jazz</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version']) ?>">
</head>
<body class="jazz-page jazz-artist-page jazz-karsu-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="jazz-artist-hero" style="background-image: linear-gradient(120deg, rgba(0,0,0,0.55), rgba(0,0,0,0.80)), url('<?= $h($viewModel->heroImage) ?>');">
        <div class="container jazz-artist-hero-content">
            <h1><?= $h($viewModel->artistTitle) ?></h1>
            <p class="jazz-artist-tagline"><?= $h($viewModel->tagline) ?></p>
        </div>
    </section>

    <section class="container jazz-artist-body jazz-karsu-centered">
        <nav class="breadcrumbs jazz-breadcrumbs">
            <a href="/">Festival</a>
            <span class="breadcrumb-sep">›</span>
            <a href="/jazz">Jazz</a>
            <span class="breadcrumb-sep">›</span>
            <span class="breadcrumb-current"><?= $h($viewModel->artistTitle) ?></span>
        </nav>

        <div class="jazz-karsu-experience-region">
        <!-- Karsu Dönmez – short bio (exact text from prototype) -->
        <article class="jazz-karsu-intro">
            <h2 class="jazz-section-title">Karsu Dönmez</h2>
            <p class="jazz-artist-bio">
                A Bridge Between Worlds Born in Amsterdam in 1990, Karsu Dönmez—known simply as Karsu—is a celebrated singer, pianist, and composer who bridges the gap between jazz, pop, and her Turkish musical roots. Her sound is a unique fusion that honors tradition while embracing a thoroughly modern edge.
            </p>
            <p class="jazz-artist-bio">
                Her career began with a childhood piano bought with family savings, sparking a passion that soon saw her performing for diners in her parents' restaurant at just 14 years old. Those intimate early gigs revealed a voice and talent that could not be contained, eventually propelling her from a local restaurant pianist to a global star gracing iconic stages like New York's Carnegie Hall.
            </p>
        </article>

        <!-- Plan your Karsu experience (layout ref: single video card + event schedule panel) -->
        <section class="jazz-plan-section jazz-karsu-plan-section">
            <h3 class="jazz-section-subtitle">Plan your <?= $h($viewModel->artistTitle) ?> experience</h3>

            <div class="jazz-karsu-video-card-wrap">
                <div class="jazz-karsu-video-block jazz-karsu-video-card">
                    <div class="jazz-karsu-video-thumb jazz-karsu-video-thumb--live">
                        <span class="jazz-karsu-video-play" aria-hidden="true">▶</span>
                    </div>
                    <?php if ($cardPreviewUrl !== ''): ?>
                        <div class="jazz-karsu-card-audio">
                            <span class="jazz-karsu-card-audio-label"><?= $h($cardPreviewTitle) ?></span>
                            <audio class="jazz-karsu-card-audio-el" controls preload="metadata" src="<?= $h($cardPreviewUrl) ?>">
                                <a href="<?= $h($cardPreviewUrl) ?>">Download audio</a>
                            </audio>
                        </div>
                    <?php endif; ?>
                    <div class="jazz-karsu-video-body">
                        <div class="jazz-karsu-video-headline">
                            <p class="jazz-karsu-video-title">Karsu</p>
                            <p class="jazz-karsu-video-meta"><?= $h($karsuVideoMeta) ?></p>
                        </div>
                        <p class="jazz-karsu-video-desc">Experience Karsu's powerful voice and captivating melodies live. - Ticket price: €<?= $h(number_format($karsuVideoPrice, 2)) ?></p>
                        <button type="button" class="jazz-btn jazz-btn-primary jazz-karsu-video-btn">Save to your program</button>
                    </div>
                </div>
            </div>

            <div class="jazz-karsu-schedule-panel">
                <div class="jazz-karsu-schedule-heading">
                    <span class="jazz-karsu-schedule-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.75"/>
                            <path d="M3 9h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <div class="jazz-karsu-schedule-heading-text">
                        <h4 class="jazz-karsu-schedule-title">Event Schedule</h4>
                        <p class="jazz-karsu-schedule-intro">Plan your experience at the festival. Choose from various performances across different venues and times.</p>
                    </div>
                </div>
                <div class="jazz-schedule jazz-karsu-schedule-table">
                    <table class="jazz-table jazz-karsu-table-buy jazz-karsu-table-schedule">
                        <thead>
                            <tr>
                                <th scope="col">Days</th>
                                <th scope="col">Time</th>
                                <th scope="col">Location</th>
                                <th scope="col">Hall</th>
                                <th scope="col">Seats</th>
                                <th scope="col">Price</th>
                                <th scope="col">Ticket</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events)): ?>
                                <tr>
                                    <td colspan="7" class="jazz-muted">No schedule rows found for this artist.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($events as $e): ?>
                                    <?php
                                    $d = strtolower($e['event_day'] ?? 'friday');
                                    $s = $e['start_time'] ?? ($app['default_event_time'] ?? '18:00');
                                    $en = $e['end_time'] ?? null;
                                    $timeStr = $en ? "{$s} - {$en}" : $s;
                                    $timeDots = str_replace(':', '.', $timeStr);
                                    $loc = $e['venue_name'] ?? '';
                                    $hall = $e['hall'] ?? '';
                                    $seats = $e['seats'] ?? null;
                                    $p = isset($e['price']) ? (float)$e['price'] : 0;
                                    ?>
                                    <tr>
                                        <td><?= $h(strtoupper($d)) ?></td>
                                        <td><?= $h($timeDots) ?></td>
                                        <td><span class="jazz-karsu-loc-underline"><?= $h(strtoupper($loc)) ?></span></td>
                                        <td><?= $hall !== '' ? $h(strtoupper($hall)) : '—' ?></td>
                                        <td><?= $seats !== null ? $h((string) $seats) : '—' ?></td>
                                        <td><?= $p > 0 ? $h(number_format($p, 2)) . ' €' : 'Free' ?></td>
                                        <td>
                                            <?php if ($p > 0): ?>
                                                <a class="jazz-karsu-btn-ticket" href="/tickets?event=<?= (int)$e['event_id'] ?>">Buy</a>
                                            <?php else: ?>
                                                <span class="jazz-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="jazz-karsu-schedule-note">Click on the ticket button to reserve your spot at the event</p>
            </div>
        </section>
        </div>

        <!-- About Karsu (exact text from prototype) -->
        <section class="jazz-highlights jazz-karsu-alt-bg">
            <h3 class="jazz-section-subtitle">About Karsu</h3>
            <p class="jazz-highlight-text">
                The Karsu Band is the driving force behind the internationally acclaimed Dutch-Turkish artist, Karsu. Renowned for their versatility and technical mastery, the ensemble is celebrated for fusing distinct musical worlds: American Jazz, Soul, Funk, and traditional Turkish folk.
            </p>
            <p class="jazz-highlight-text">
                The band's signature sound is characterized by a rich interplay between Western instrumentation (drums, electric bass, brass) and Eastern scales and rhythms. Whether interpreting a classic Atlantic Records soul hit or reimagining a Turkish folk standard, the musicians create a cohesive, cinematic soundscape. This cross-cultural synergy allows them to navigate complex arrangements with ease, moving from intimate, piano-driven ballads to explosive, horn-heavy funk jams that bring audiences to their feet.
            </p>
        </section>

        <!-- Karsu's Music (exact text from prototype) -->
        <section class="jazz-highlights jazz-karsu-alt-bg">
            <h3 class="jazz-section-subtitle">Karsu's Music</h3>
            <p class="jazz-highlight-text">
                Karsu is a dynamic Dutch-Turkish singer, pianist, and composer who bridges East and West. Her sound is a unique fusion of American Jazz and Blues, modern Pop, and traditional Turkish folk.
            </p>
            <ul class="jazz-karsu-list">
                <li><strong>The Vibe:</strong> Ranges from smoky, intimate piano ballads to high-energy, rhythmic funk and danceable pop.</li>
                <li><strong>The Voice:</strong> Powerful and versatile, she sings fluidly in <strong>English, Turkish, and Dutch</strong>.</li>
                <li><strong>The Experience:</strong> Known for her virtuoso piano playing and electrifying stage presence, she blends Western grooves with Eastern scales to create a sound that is both global and deeply personal.</li>
            </ul>
        </section>

        <!-- Why You'll Love Karsu (exact text from prototype) -->
        <section class="jazz-highlights jazz-karsu-alt-bg">
            <h3 class="jazz-section-subtitle">Why You'll Love Karsu</h3>
            <ul class="jazz-karsu-list">
                <li><strong>A Unique Musical Fusion:</strong> She seamlessly blends the cool sophistication of American jazz and blues with the rich, emotional depth of traditional Turkish melodies.</li>
                <li><strong>Virtuoso Talent:</strong> She is a "triple threat"—an exceptional singer with a powerful voice, a technically brilliant classical/jazz pianist, and a skilled composer.</li>
                <li><strong>Emotional Connection:</strong> Whether singing in English, Turkish, or Dutch, she is a master storyteller who creates an intimate atmosphere, making you feel every note.</li>
                <li><strong>Electric Stage Presence:</strong> Her concerts are energetic and unpredictable; she transitions effortlessly from melancholic ballads that make you cry to upbeat dance tracks that get the whole room moving.</li>
            </ul>
        </section>

        <!-- Career Highlights (exact text from prototype) -->
        <section class="jazz-highlights jazz-karsu-alt-bg">
            <h3 class="jazz-section-subtitle">Career Highlights</h3>
            <p class="jazz-highlight-text">
                Karsu's rise from performing in her family's Amsterdam restaurant to gracing New York's Carnegie Hall three times before the age of 20 is nothing short of remarkable. An Edison Award-winning artist, she has solidified her reputation as a live sensation, captivating audiences at major international venues like the North Sea Jazz Festival and Istanbul Jazz Festival.
            </p>
            <p class="jazz-highlight-text">
                Beyond her musical success, she became a powerful symbol of resilience as the face of the 2023 Dutch earthquake relief campaign, helping to raise millions for survivors. Today, she continues to evolve as a multifaceted star, recently launching her acclaimed 2025 album Tabula Rasa while celebrating success as a bestselling author.
            </p>
        </section>

        <!-- Discography (data from artist_discography) -->
        <section
            class="jazz-karsu-discography jazz-karsu-disco-shell"
            id="jazzKarsuDiscography"
            data-tracks="<?= $h($discographyJson) ?>"
        >
            <h3 class="jazz-karsu-disco-heading">Discography:</h3>

            <?php if (count($discography) === 0): ?>
                <p class="jazz-karsu-disco-empty jazz-muted">Discography will appear here after the database is seeded (run migrations and <code>KarsuDiscographySeeder</code>).</p>
            <?php else: ?>
                <div class="jazz-karsu-disco-stage">
                    <div class="jazz-karsu-disco-orangerow">
                        <div class="jazz-karsu-disco-covers" role="group" aria-label="Album covers">
                            <button type="button" class="jazz-karsu-disco-cover-btn" data-slot="left" aria-label="Previous album">
                                <img src="" alt="" class="jazz-karsu-disco-cover-img" width="200" height="200">
                            </button>
                            <button type="button" class="jazz-karsu-disco-cover-btn jazz-karsu-disco-cover-btn--focus" data-slot="center" aria-label="Current album">
                                <img src="" alt="" class="jazz-karsu-disco-cover-img" width="280" height="280">
                            </button>
                            <button type="button" class="jazz-karsu-disco-cover-btn" data-slot="right" aria-label="Next album">
                                <img src="" alt="" class="jazz-karsu-disco-cover-img" width="200" height="200">
                            </button>
                        </div>
                        <div class="jazz-karsu-disco-panel">
                            <div class="jazz-karsu-disco-detail">
                                <p class="jazz-karsu-disco-title" id="jazzKarsuDiscoTitle"></p>
                                <p class="jazz-karsu-disco-meta" id="jazzKarsuDiscoMeta"></p>
                                <p class="jazz-karsu-disco-plays" id="jazzKarsuDiscoPlays"></p>
                            </div>
                            <div class="jazz-karsu-disco-controls">
                                <button type="button" class="jazz-karsu-disco-ctrl jazz-karsu-disco-ctrl--side" id="jazzKarsuDiscoPrev" aria-label="Previous track">
                                    <svg class="jazz-karsu-disco-ctrl-svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M20 6L20 18 12 12 20 6zm-9 0L11 18 3 12 11 6z"/></svg>
                                </button>
                                <button type="button" class="jazz-karsu-disco-ctrl jazz-karsu-disco-ctrl--play" id="jazzKarsuDiscoPlay" aria-label="Play">
                                    <svg class="jazz-karsu-disco-icon-play" viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path fill="currentColor" d="M8 5v14l11-7z"/></svg>
                                    <svg class="jazz-karsu-disco-icon-pause" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" hidden><path fill="currentColor" d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>
                                <button type="button" class="jazz-karsu-disco-ctrl jazz-karsu-disco-ctrl--side" id="jazzKarsuDiscoNext" aria-label="Next track">
                                    <svg class="jazz-karsu-disco-ctrl-svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M4 6L4 18 12 12 4 6zm9 0L13 18 21 12 13 6z"/></svg>
                                </button>
                            </div>
                            <div class="jazz-karsu-disco-progress" id="jazzKarsuDiscoProgressWrap" hidden>
                                <div class="jazz-karsu-disco-progress-bar" id="jazzKarsuDiscoProgressBar"></div>
                            </div>
                        </div>
                    </div>
                    <audio id="jazzKarsuDiscoAudio" preload="metadata"></audio>
                </div>
            <?php endif; ?>

            <?php if (count($discography) > 0): ?>
                <div class="jazz-karsu-disco-back-wrap">
                    <a class="jazz-karsu-disco-back" href="/jazz">&lt; BACK</a>
                </div>
            <?php endif; ?>
        </section>

        <?php if (count($discography) === 0): ?>
            <div class="jazz-back">
                <a class="jazz-back-btn" href="/jazz">‹ Back to Jazz</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<?php if (count($discography) > 0): ?>
<script>
(function () {
    var root = document.getElementById('jazzKarsuDiscography');
    if (!root) return;
    var raw = root.getAttribute('data-tracks');
    if (!raw) return;
    var tracks;
    try { tracks = JSON.parse(raw); } catch (e) { return; }
    if (!tracks || !tracks.length) return;

    var n = tracks.length;
    var active = Math.min(1, n - 1);
    if (n === 1) active = 0;

    var audio = document.getElementById('jazzKarsuDiscoAudio');
    var btnPlay = document.getElementById('jazzKarsuDiscoPlay');
    var iconPlay = btnPlay ? btnPlay.querySelector('.jazz-karsu-disco-icon-play') : null;
    var iconPause = btnPlay ? btnPlay.querySelector('.jazz-karsu-disco-icon-pause') : null;

    function setPlayingUi(playing) {
        if (!btnPlay) return;
        if (iconPlay) iconPlay.hidden = !!playing;
        if (iconPause) iconPause.hidden = !playing;
        btnPlay.setAttribute('aria-label', playing ? 'Pause' : 'Play');
    }
    var btnPrev = document.getElementById('jazzKarsuDiscoPrev');
    var btnNext = document.getElementById('jazzKarsuDiscoNext');
    var elTitle = document.getElementById('jazzKarsuDiscoTitle');
    var elMeta = document.getElementById('jazzKarsuDiscoMeta');
    var elPlays = document.getElementById('jazzKarsuDiscoPlays');
    var progressWrap = document.getElementById('jazzKarsuDiscoProgressWrap');
    var progressBar = document.getElementById('jazzKarsuDiscoProgressBar');
    var coverBtns = root.querySelectorAll('.jazz-karsu-disco-cover-btn');

    function fmtTime(sec) {
        if (sec == null || !isFinite(sec)) return '—';
        var s = Math.floor(sec);
        var m = Math.floor(s / 60);
        s = s % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function idx(i) {
        return ((i % n) + n) % n;
    }

    function setAudioSrc(url) {
        if (!audio) return;
        try {
            audio.pause();
        } catch (e) {}
        audio.src = url;
        audio.load();
        setPlayingUi(false);
        if (progressBar) progressBar.style.width = '0%';
    }

    function render() {
        var leftI = idx(active - 1);
        var curI = idx(active);
        var rightI = idx(active + 1);
        var order = [leftI, curI, rightI];
        var slots = ['left', 'center', 'right'];
        coverBtns.forEach(function (btn) {
            var slot = btn.getAttribute('data-slot');
            var si = slots.indexOf(slot);
            if (si < 0) return;
            var t = tracks[order[si]];
            var img = btn.querySelector('img');
            if (img) {
                img.src = t.imageUrl;
                img.alt = t.title || 'Album cover';
            }
            btn.classList.toggle('jazz-karsu-disco-cover-btn--focus', slot === 'center');
        });

        var cur = tracks[curI];
        if (elTitle) elTitle.textContent = cur.title || '';
        var year = cur.releaseYear != null ? String(cur.releaseYear) : '—';
        var dur = fmtTime(cur.durationSeconds);
        if (elMeta) elMeta.textContent = '\u2014 ' + year + ' | Playtime ' + dur;
        if (elPlays) elPlays.textContent = 'Played ' + Number(cur.playCount || 0).toLocaleString('en-US') + ' times';

        setAudioSrc(cur.audioUrl);
    }

    function togglePlay() {
        if (!audio || !btnPlay) return;
        if (audio.paused) {
            audio.play().then(function () {
                setPlayingUi(true);
            }).catch(function () {});
        } else {
            audio.pause();
            setPlayingUi(false);
        }
    }

    if (btnPlay) btnPlay.addEventListener('click', togglePlay);
    if (btnPrev) btnPrev.addEventListener('click', function () {
        active = idx(active - 1);
        render();
    });
    if (btnNext) btnNext.addEventListener('click', function () {
        active = idx(active + 1);
        render();
    });

    coverBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var slot = btn.getAttribute('data-slot');
            if (slot === 'left') active = idx(active - 1);
            else if (slot === 'right') active = idx(active + 1);
            render();
        });
    });

    if (audio) {
        audio.addEventListener('play', function () { setPlayingUi(true); });
        audio.addEventListener('pause', function () { setPlayingUi(false); });
        audio.addEventListener('ended', function () { setPlayingUi(false); });
        audio.addEventListener('timeupdate', function () {
            if (!progressBar || !audio.duration) return;
            var p = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = Math.min(100, p) + '%';
        });
        audio.addEventListener('loadedmetadata', function () {
            if (progressWrap) progressWrap.hidden = false;
        });
    }

    render();
})();
</script>
<?php endif; ?>

</body>
</html>
