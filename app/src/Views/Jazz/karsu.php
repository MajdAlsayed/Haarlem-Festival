<?php
/** @var \App\ViewModels\JazzArtistViewModel $viewModel */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$events = $viewModel->events;
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

        <!-- Plan your Karsu experience -->
        <section class="jazz-plan-section">
            <h3 class="jazz-section-subtitle">Plan your <?= $h($viewModel->artistTitle) ?> experience</h3>

            <div class="jazz-karsu-plan-layout">
                <div class="jazz-karsu-video-block">
                    <div class="jazz-karsu-video-thumb">
                        <span class="jazz-karsu-video-play" aria-hidden="true">▶</span>
                    </div>
                    <p class="jazz-karsu-video-title">Karsu</p>
                    <p class="jazz-karsu-video-meta">Date: TBD | Time: TBD | Patronaat, Main hall</p>
                    <p class="jazz-karsu-video-desc">Experience Karsu's powerful voice and captivating melodies live - Ticket price: €23.00</p>
                    <button type="button" class="jazz-btn jazz-btn-primary jazz-karsu-video-btn">Add to your program</button>
                </div>

                <div class="jazz-karsu-schedule-block">
                    <p class="jazz-karsu-schedule-intro">Plan your experience at the festival. Choose from various performances across different venues and times.</p>
                    <div class="jazz-schedule jazz-karsu-schedule-table">
                        <table class="jazz-table jazz-table-buy">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Hall</th>
                                    <th>Seats</th>
                                    <th>Price</th>
                                    <th></th>
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
                                        $dayAbbr = strtoupper(substr($d, 0, 3));
                                        if ($d === 'wednesday') $dayAbbr = 'WED';
                                        if ($d === 'thursday') $dayAbbr = 'THU';
                                        if ($d === 'friday') $dayAbbr = 'FRI';
                                        if ($d === 'saturday') $dayAbbr = 'SAT';
                                        if ($d === 'sunday') $dayAbbr = 'SUN';
                                        $s = $e['start_time'] ?? ($app['default_event_time'] ?? '18:00');
                                        $en = $e['end_time'] ?? null;
                                        $timeStr = $en ? "{$s} - {$en}" : $s;
                                        $loc = $e['venue_name'] ?? '';
                                        $hall = $e['hall'] ?? '';
                                        $seats = $e['seats'] ?? null;
                                        $p = isset($e['price']) ? (float)$e['price'] : 0;
                                        ?>
                                        <tr>
                                            <td><?= $h(ucfirst($d)) ?></td>
                                            <td><?= $h($timeStr) ?></td>
                                            <td><?= $h(strtoupper($loc)) ?></td>
                                            <td><?= $hall !== '' ? $h(strtoupper($hall)) : '—' ?></td>
                                            <td><?= $seats !== null ? $h($seats) : '—' ?></td>
                                            <td><?= $p > 0 ? '€ ' . $h(number_format($p, 2)) : 'Free' ?></td>
                                            <td>
                                                <?php if ($p > 0): ?>
                                                    <a class="jazz-btn jazz-btn-buy" href="/tickets?event=<?= (int)$e['event_id'] ?>">Buy</a>
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
                    <p class="jazz-karsu-schedule-note">Click on the 'Add to program' button to reserve your spot on the lineup.</p>
                </div>
            </div>
        </section>

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

        <!-- Discography -->
        <section class="jazz-karsu-discography jazz-karsu-alt-bg">
            <h3 class="jazz-section-subtitle">Discography:</h3>
            <div class="jazz-karsu-albums">
                <div class="jazz-karsu-album"><img src="/images/jazz/hero-karsu.jpg" alt="Karsu album" class="jazz-karsu-album-cover"></div>
                <div class="jazz-karsu-album"><img src="/images/jazz/hero-karsu.jpg" alt="Karsu album" class="jazz-karsu-album-cover"></div>
                <div class="jazz-karsu-album"><img src="/images/jazz/hero-karsu.jpg" alt="Karsu album" class="jazz-karsu-album-cover"></div>
            </div>
            <p class="jazz-karsu-track-info">2020 | KARSU DÖNMEZ UWLANTUN | Playtime: 3:45 | Listen here</p>
            <div class="jazz-karsu-player">
                <button type="button" class="jazz-karsu-player-btn" aria-label="Previous track">‹</button>
                <button type="button" class="jazz-karsu-player-btn jazz-karsu-player-play" aria-label="Play">▶</button>
                <button type="button" class="jazz-karsu-player-btn" aria-label="Next track">›</button>
            </div>
            <div class="jazz-karsu-progress">
                <div class="jazz-karsu-progress-bar"></div>
            </div>
            <a href="#" class="jazz-btn jazz-btn-primary jazz-karsu-listen-btn">LISTEN ALL</a>
        </section>

        <div class="jazz-back">
            <a class="jazz-back-btn" href="/jazz">‹ Back to Jazz</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
