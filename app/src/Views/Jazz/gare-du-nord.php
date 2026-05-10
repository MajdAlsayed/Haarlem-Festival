<?php
/**
 * Gare du Nord artist page (/jazz/gare-du-nord) — film-strip style hero, venue blurb, then schedule + cart like the others.
 *
 * JazzController::gareDuNord() passes the usual view model; this file also builds the alternating B/W vs colour strip
 * assets and picks a sensible map link from the venue name.
 */
/** @var \App\ViewModels\JazzArtistViewModel $viewModel */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$jazzConfig = require __DIR__ . '/../../Config/jazz.php';

/** @var array<int, array<string, mixed>> $events */
$events = $viewModel->events;
$jazzCartReturnUrl = '/jazz/' . rawurlencode($viewModel->slug);

$cardFile = $jazzConfig['event_card_images']['Gare du Nord'] ?? 'Gare-du-nord-event.png';
$cardImage = '/images/jazz/' . rawurlencode($cardFile);
$heroImage = '/images/jazz/gare-du-nord-hero.jpg';

$dayToDateLabel = [
    'thursday' => 'Thursday 24 July',
    'friday' => 'Friday 25 July',
    'saturday' => 'Saturday 26 July',
    'sunday' => 'Sunday 27 July',
];

$gareVenueAddress = static function (string $venueName, string $city): string {
    if (stripos($venueName, 'patronaat') !== false) {
        return 'Zijlsingel 2, 2013 DN Haarlem';
    }

    return $city !== '' ? $city : 'Haarlem';
};

$introBio = trim($viewModel->bio);
$isPlaceholderBio = $introBio === '' || str_contains(strtolower($introBio), 'placeholder') || str_contains(strtolower($introBio), 'store real bio');

/** @var list<array<string, mixed>> $discography */
$discography = $viewModel->discography;
$firstEventPreview = null;
foreach ($events as $ev) {
    $pa = $ev['preview_audio'] ?? null;
    if (is_array($pa) && !empty($pa['url'])) {
        $firstEventPreview = $pa;
        break;
    }
}

// Settings for the page title, styles, body class
$pageTitle = $viewModel->artistTitle . ' — Jazz — Haarlem Festival';
$pageStyles = ['/css/pages/jazz.css'];
$bodyClass = 'jazz-page jazz-artist-page jazz-gare-page';

// Breadcrumbs
$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Jazz', 'url' => '/jazz'],
        ['label' => $viewModel->artistTitle, 'url' => null],
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <?php
    $cartFlash = \App\Core\Session::getFlash('cart_success');
    $cartFlashError = \App\Core\Session::getFlash('cart_error');
    ?>
    <?php if (!empty($cartFlash)): ?>
        <div class="container jazz-cart-flash jazz-cart-flash--success" role="status"><?= $h($cartFlash) ?></div>
    <?php endif; ?>
    <?php if (!empty($cartFlashError)): ?>
        <div class="container jazz-cart-flash jazz-cart-flash--error" role="alert"><?= $h($cartFlashError) ?></div>
    <?php endif; ?>
    <section class="page-hero jazz-detail-hero jazz-gare-detail-hero" aria-label="<?= $h($viewModel->artistTitle) ?> hero">
        <div class="page-hero__background">
            <img
                    src="<?= $h($heroImage) ?>"
                    alt="<?= $h($viewModel->artistTitle) ?>"
            >
        </div>

        <div class="container page-hero__inner">
            <div class="page-hero__content jazz-detail-hero__content jazz-gare-detail-hero__content">
                <h1 class="page-hero__title">
                    <?= $h($viewModel->artistTitle) ?>
                </h1>

                <?php if (trim((string) $viewModel->tagline) !== ''): ?>
                    <p class="page-hero__subtitle">
                        <?= $h($viewModel->tagline) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <section class="container jazz-artist-body jazz-gare-body">
        <article class="jazz-gare-intro">
            <h2 class="jazz-section-title"><?= $h($viewModel->artistTitle) ?></h2>
            <?php
            $pageIntro = trim($viewModel->pageIntroText);
            ?>
            <?php if ($pageIntro !== ''): ?>
                <p class="jazz-artist-bio jazz-gare-bio"><?= nl2br($h($pageIntro)) ?></p>
            <?php elseif ($isPlaceholderBio): ?>
                <p class="jazz-artist-bio jazz-gare-bio">
                    Gare du Nord are Dutch jazz royalty: a cinematic soul collective rooted in the urban lounge, where film-noir atmosphere meets tight grooves and luminous vocals. For years their sound has defined late-night Haarlem and beyond—smoky hooks, brass shimmer, and a stage presence that feels both intimate and immense.
                </p>
            <?php else: ?>
                <p class="jazz-artist-bio jazz-gare-bio"><?= nl2br($h($introBio)) ?></p>
            <?php endif; ?>
        </article>

        <section class="jazz-plan-section jazz-gare-plan">
            <h3 class="jazz-section-subtitle">Plan your <?= $h($viewModel->artistTitle) ?> experience</h3>

            <div class="jazz-gare-media-block">
                <?php if ($discography !== []): ?>
                    <p class="jazz-gare-media-lead">Discography — listen to tracks for this artist.</p>
                    <div class="jazz-gumbo-experience-outer jazz-gare-discography-cards">
                        <div class="jazz-gumbo-cards jazz-gumbo-experience-grid">
                            <?php
                            $discCoverAlternates = [$cardImage, $viewModel->heroImage];
                            require __DIR__ . '/partials/discography-experience-cards.php';
                            ?>
                        </div>
                    </div>
                <?php elseif ($firstEventPreview !== null): ?>
                    <p class="jazz-gare-media-lead">Preview — from the jazz event (admin → Jazz → Events → preview audio).</p>
                    <div class="jazz-gare-disc-row jazz-gare-disc-row--preview">
                        <div class="jazz-gare-disc-cover jazz-gare-disc-cover--hero">
                            <img src="<?= $h($viewModel->heroImage) ?>" alt="" loading="lazy" decoding="async">
                        </div>
                        <div class="jazz-gare-disc-body">
                            <?php if (!empty($firstEventPreview['track_title'])): ?>
                                <div class="jazz-gare-disc-title"><?= $h((string) $firstEventPreview['track_title']) ?></div>
                            <?php endif; ?>
                            <audio class="jazz-gare-audio" controls preload="metadata" src="<?= $h((string) $firstEventPreview['url']) ?>"></audio>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="jazz-gare-video-block">
                        <a class="jazz-gare-video-link"
                           href="https://www.youtube.com/results?search_query=Gare+du+Nord+jazz+band+live"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="Watch Gare du Nord on YouTube (opens in a new tab)">
                            <span class="jazz-gare-video-poster" style="background-image: url('<?= $h($viewModel->heroImage) ?>');"></span>
                            <span class="jazz-gare-video-glow" aria-hidden="true"></span>
                            <span class="jazz-gare-video-play" aria-hidden="true">
                                <span class="jazz-gare-video-play-icon">▶</span>
                            </span>
                        </a>
                    </div>
                    <p class="jazz-gare-media-fallback-hint jazz-muted">Add audio under <strong>Admin → Jazz → Discography</strong> with artist slug exactly <code>gare-du-nord</code>, or set <strong>preview audio</strong> on a Gare du Nord event.</p>
                <?php endif; ?>
            </div>

            <div class="jazz-schedule jazz-schedule--artist-detail jazz-gare-schedule" aria-label="Event schedule from Jazz admin">
                <table class="jazz-table jazz-table-buy jazz-gare-table">
                    <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                        <th scope="col">Location</th>
                        <th scope="col">Address</th>
                        <th scope="col">Hall</th>
                        <th scope="col">Price</th>
                        <th scope="col">Tickets</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="7" class="jazz-muted">No schedule rows found in the database yet for this artist.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $e): ?>
                            <?php
                            $d = strtolower((string) ($e['event_day'] ?? 'friday'));
                            $dateLabel = $dayToDateLabel[$d] ?? ucfirst($d);
                            $s = $e['start_time'] ?? ($app['default_event_time'] ?? '18:00');
                            $en = $e['end_time'] ?? null;
                            $tr = $en ? "{$s} – {$en}" : $s;
                            $venueName = (string) ($e['venue_name'] ?? '');
                            $city = (string) ($e['venue_city'] ?? '');
                            $address = $gareVenueAddress($venueName, $city);
                            $hall = isset($e['hall']) && $e['hall'] !== '' ? (string) $e['hall'] : '—';
                            $p = $e['price'] ?? null;
                            $priceLabel = ($p !== null && (float) $p > 0)
                                ? '€ ' . $h(number_format((float) $p, 2))
                                : 'Free';
                            ?>
                            <tr>
                                <td><?= $h(strtoupper($dateLabel)) ?></td>
                                <td><?= $h($tr) ?></td>
                                <td><?= $h($venueName) ?></td>
                                <td><?= $h($address) ?></td>
                                <td><?= $h($hall) ?></td>
                                <td><?= $priceLabel ?></td>
                                <td>
                                    <?php if ($p !== null && (float) $p > 0): ?>
                                        <?php
                                        $ticketDetailsId = (int) ($e['ticket_details_id'] ?? 0);
                                        $returnUrl = $jazzCartReturnUrl;
                                        $buttonClass = 'jazz-btn jazz-btn-primary jazz-gare-buy';
                                        $buttonLabel = 'Add to program';
                                        require __DIR__ . '/partials/jazz-add-to-cart-form.php';
                                        ?>
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
        </section>

        <section class="jazz-highlights jazz-gare-highlights">
            <h3 class="jazz-section-subtitle">Career highlights</h3>
            <?php if (trim($viewModel->careerHighlightsHtml) !== ''): ?>
                <div class="jazz-gare-highlights-cms">
                    <?= \App\Services\JazzArtistHtmlSanitizer::purifyHighlights($viewModel->careerHighlightsHtml) ?>
                </div>
            <?php else: ?>
            <p class="jazz-highlight-text">
                Gare du Nord helped shape the Dutch live circuit with a signature blend of soul, jazz, and soundtrack drama—always cinematic, always danceable. Their recordings and festival appearances built a loyal following that expects both velvet melancholy and explosive release in a single set.
            </p>
            <p class="jazz-highlight-text">
                The collective became known for the <strong class="jazz-gare-accent">Sex ’n’ Jazz</strong> phenomenon: a long-running celebration where groove, glamour, and improvisation collided night after night. That era cemented their reputation as architects of the modern urban-lounge sound.
            </p>
            <p class="jazz-highlight-text">
                After time away from the spotlight, Gare du Nord returned to the stage with renewed fire—proving that their chemistry, storytelling, and sonic identity still resonate with audiences who want jazz that feels like a film score you can move to.
            </p>
            <?php endif; ?>
        </section>

        <?php
        $members = $viewModel->bandMembers;
        require __DIR__ . '/partials/band-members-section.php';
        ?>

        <div class="jazz-back jazz-gare-back">
            <a class="jazz-back-btn jazz-gare-back-btn" href="/jazz">Back</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
    document.querySelectorAll('.jazz-gumbo-experience-card').forEach(function (card) {
        var btn = card.querySelector('.jazz-gumbo-exp-play');
        var audio = card.querySelector('.jazz-gumbo-exp-audio');
        if (!btn || !audio) return;
        var icon = btn.querySelector('.jazz-gumbo-exp-play-icon');
        btn.addEventListener('click', function () {
            document.querySelectorAll('.jazz-gumbo-exp-audio').forEach(function (a) {
                if (a !== audio) {
                    a.pause();
                    a.currentTime = 0;
                    var ob = a.closest('.jazz-gumbo-experience-card');
                    if (ob) {
                        var pb = ob.querySelector('.jazz-gumbo-exp-play');
                        var ic = ob.querySelector('.jazz-gumbo-exp-play-icon');
                        if (pb) pb.classList.remove('is-playing');
                        if (ic) ic.textContent = '▶';
                    }
                }
            });
            if (audio.paused) {
                audio.play().catch(function () {});
                btn.classList.add('is-playing');
                if (icon) icon.textContent = '⏸';
            } else {
                audio.pause();
                btn.classList.remove('is-playing');
                if (icon) icon.textContent = '▶';
            }
        });
        audio.addEventListener('ended', function () {
            btn.classList.remove('is-playing');
            if (icon) icon.textContent = '▶';
        });
    });
})();
</script>
</body>
</html>
