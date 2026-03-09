<?php
/** @var \App\ViewModels\JazzArtistViewModel $viewModel */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$events = $viewModel->events;
$primary = $events[0] ?? null;

$day      = $primary['event_day'] ?? null;
$start    = $primary['start_time'] ?? null;
$end      = $primary['end_time'] ?? null;
$venue    = $primary['venue_name'] ?? null;
$hall     = $primary['hall'] ?? null;
$price    = $primary['price'] ?? null;

$timeRange = $start ? ($end ? "{$start} - {$end}" : $start) : ($app['default_event_time'] ?? null);
$location  = trim(($venue ?? '') . ($hall ? " ({$hall})" : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $h($viewModel->artistTitle) ?> — Jazz</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version']) ?>">
</head>
<body class="jazz-page jazz-artist-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <section class="jazz-artist-hero" style="background-image: linear-gradient(120deg, rgba(0,0,0,0.55), rgba(0,0,0,0.80)), url('<?= $h($viewModel->heroImage) ?>');">
        <div class="container jazz-artist-hero-content">
            <h1><?= $h($viewModel->artistTitle) ?></h1>
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

        <article class="jazz-artist-overview">
            <h2 class="jazz-section-title"><?= $h($viewModel->artistTitle) ?></h2>
            <p class="jazz-artist-bio"><?= nl2br($h($viewModel->bio)) ?></p>
        </article>

        <section class="jazz-plan-section">
            <h3 class="jazz-section-subtitle">Plan your <?= $h($viewModel->artistTitle) ?> experience</h3>

            <div class="jazz-plan-layout">
                <div class="jazz-plan-media-grid">
                    <div class="jazz-plan-card">
                        <img src="<?= $h($viewModel->heroImage) ?>" alt="" class="jazz-plan-card-img">
                        <h4 class="jazz-plan-card-title">Urban lounge atmosphere</h4>
                        <p class="jazz-plan-card-text">
                            Use this section to highlight the cinematic soul and lounge vibe of Gare du Nord’s show.
                        </p>
                    </div>
                    <div class="jazz-plan-card">
                        <img src="<?= $h($viewModel->heroImage) ?>" alt="" class="jazz-plan-card-img">
                        <h4 class="jazz-plan-card-title">Make it a full night</h4>
                        <p class="jazz-plan-card-text">
                            Add copy about other festival events, afterparties, or nearby experiences.
                        </p>
                    </div>
                </div>

                <div class="jazz-schedule">
                    <table class="jazz-table">
                        <thead>
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="4" class="jazz-muted">
                                    No schedule rows found in the database yet for this artist.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $e): ?>
                                <?php
                                    $d = $e['event_day'] ?? 'friday';
                                    $s = $e['start_time'] ?? ($app['default_event_time'] ?? '18:00');
                                    $en = $e['end_time'] ?? null;
                                    $tr = $en ? "{$s} - {$en}" : $s;
                                    $loc = trim(($e['venue_name'] ?? '') . (!empty($e['hall']) ? ' (' . $e['hall'] . ')' : ''));
                                    $p = $e['price'] ?? null;
                                ?>
                                <tr>
                                    <td><?= $h(ucfirst($d)) ?></td>
                                    <td><?= $h($tr) ?></td>
                                    <td><?= $h($loc) ?></td>
                                    <td>
                                        <?php if ($p !== null && (float)$p > 0): ?>
                                            <?= $h(number_format((float)$p, 2)) ?>€
                                        <?php else: ?>
                                            Free
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($timeRange || $location): ?>
                <p class="jazz-primary-slot">
                    <strong>Performance:</strong>
                    <?php if ($day): ?><?= $h(ucfirst($day)) ?><?php endif; ?>
                    <?php if ($timeRange): ?> • <?= $h($timeRange) ?><?php endif; ?>
                    <?php if ($location): ?> • <?= $h($location) ?><?php endif; ?>
                </p>
            <?php endif; ?>
        </section>

        <section class="jazz-highlights">
            <h3 class="jazz-section-subtitle">Career highlights</h3>
            <p class="jazz-highlight-text">
                Replace this block with real highlights of Gare du Nord: albums, tours and key collaborations.
            </p>
        </section>

        <section class="jazz-band">
            <h3 class="jazz-section-subtitle">Band members</h3>
            <div class="jazz-band-grid">
                <?php
                $members = [
                    ['name' => 'Lead vocalist', 'role' => 'Update with real member name'],
                    ['name' => 'Guitar', 'role' => 'Update with real member name'],
                    ['name' => 'Drums', 'role' => 'Update with real member name'],
                    ['name' => 'Bass', 'role' => 'Update with real member name'],
                    ['name' => 'Keys', 'role' => 'Update with real member name'],
                    ['name' => 'Saxophone', 'role' => 'Update with real member name'],
                ];
                foreach ($members as $m):
                ?>
                    <article class="jazz-band-card">
                        <h4 class="jazz-band-name"><?= $h($m['name']) ?></h4>
                        <p class="jazz-band-role"><?= $h($m['role']) ?></p>
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

</body>
</html>