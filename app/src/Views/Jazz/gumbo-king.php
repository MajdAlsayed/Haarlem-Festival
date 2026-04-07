<?php
/**
 * Gumbo Kings artist page (/jazz/gumbo-kings).
 *
 * JazzController::gumboKings() fills the view model: intro text, discography strip, timetable, and for ticketed rows
 * a small form that posts to the cart (“Add to program”). Flash lines at the top say if that worked or not.
 */
/** @var \App\ViewModels\JazzArtistViewModel $viewModel */

$app = (new \App\Repositories\SettingsRepository())->getAll();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$skipHeaderStyleSheet = true;
$jazzCartReturnUrl = '/jazz/' . rawurlencode($viewModel->slug);
$jazzConfig = require __DIR__ . '/../../Config/jazz.php';
$gumboCardImage = '/images/jazz/' . rawurlencode($jazzConfig['event_card_images']['Gumbo Kings'] ?? 'Gumbo-king-cover-page-and-event.png');

/** @var array<int, array<string,mixed>> $events */
$events = $viewModel->events;
/** @var list<array<string,mixed>> $discography */
$discography = $viewModel->discography;

// Human-readable date line per weekday (matches the festival weekend we’re selling)
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css?v=<?= $h($app['css_version']) ?>&gumbo=22">
</head>
<body class="jazz-page jazz-artist-page jazz-gumbo-page">

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
            <?php if (trim($viewModel->pageIntroText) !== ''): ?>
            <p class="jazz-artist-bio jazz-gumbo-bio-justified"><?= nl2br($h(trim($viewModel->pageIntroText))) ?></p>
            <?php else: ?>
            <p class="jazz-artist-bio jazz-gumbo-bio-justified">
                Gumbo Kings is a dynamic five-piece band from the Netherlands, known for their modern yet soulful interpretation of Rhythm 'n Blues and New Orleans Groove. Inspired by legends of Memphis Soul and Delta Blues, they deliver an energetic mix of raw sound, smooth melodies, and timeless musical vibes. With their powerful stage presence and engaging performances, the Gumbo Kings have become a festival favorite, breathing life into jazz traditions with a contemporary twist.
            </p>
            <?php endif; ?>
        </article>

        <!-- Discography row + full schedule (with add-to-cart on paid slots) -->
        <section class="jazz-plan-section jazz-gumbo-plan">
            <h3 class="jazz-section-subtitle">Plan Your Gumbo Kings Experience</h3>

            <?php
            $discCoverAlternates = [$gumboCardImage, $viewModel->heroImage];
            ?>
            <?php if ($discography !== []): ?>
            <h4 class="jazz-gumbo-subblock-title">Discography</h4>
            <div class="jazz-gumbo-experience-outer jazz-gumbo-discography-outer">
            <div class="jazz-gumbo-cards jazz-gumbo-experience-grid">
                <?php require __DIR__ . '/partials/discography-experience-cards.php'; ?>
            </div>
            </div>
            <?php endif; ?>

            <div class="jazz-gumbo-schedule-wrap">
                <p class="jazz-gumbo-plan-visit"><span class="jazz-gumbo-cal-icon" aria-hidden="true">📅</span> Plan Your Visit</p>
                <div class="jazz-schedule jazz-schedule--artist-detail">
                    <table class="jazz-table jazz-gumbo-table">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                                <th scope="col">Location</th>
                                <th scope="col">Price</th>
                                <th scope="col">Tickets</th>
                                <th scope="col">Action</th>
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
                                            <?php if ($p > 0): ?>
                                                <?php
                                                $ticketDetailsId = (int) ($e['ticket_details_id'] ?? 0);
                                                $returnUrl = $jazzCartReturnUrl;
                                                $buttonClass = 'jazz-btn jazz-btn-primary jazz-gumbo-add-btn';
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
                <p class="jazz-gumbo-schedule-note">Add to program puts the ticket in your cart; you stay on this page and see a short confirmation above. All tickets are subject to availability.</p>
            </div>
        </section>

        <!-- Career Highlights (prototype: orange years, left-aligned) -->
        <section class="jazz-highlights jazz-gumbo-highlights">
            <h3 class="jazz-section-subtitle">Career Highlights</h3>
            <?php if (trim($viewModel->careerHighlightsHtml) !== ''): ?>
                <div class="jazz-gumbo-highlights-cms">
                    <?= \App\Services\JazzArtistHtmlSanitizer::purifyHighlights($viewModel->careerHighlightsHtml) ?>
                </div>
            <?php else: ?>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2019</strong> — Earned the title of "most booked band" at Popronde, the Netherlands' leading traveling music festival. This distinction highlighted their growing momentum and broad appeal to audiences in cities across the country.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2020</strong> — Delivered a milestone performance on the prestigious NPO Soul & Jazz stage at Noorderslag. This appearance at one of the biggest Dutch showcase events cemented their status as a standout talent within the jazz and soul community.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2021</strong> — Released the acclaimed single "Hurtin'," a track that continued their signature fusion of modern Rhythm 'n Blues with the vintage sounds of New Orleans and Memphis.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">2022</strong> — Unveiled their debut album, In The Dark, produced by Paul Willemsen. The record was praised for its authentic yet modern vibe, featuring a dynamic range of soulful ballads and high-octane tracks.</p>
            <p class="jazz-highlight-text"><strong class="jazz-gumbo-year">Live Performances</strong> — Renowned for their high-voltage stage energy, the Gumbo Kings have thrilled crowds at iconic venues such as Paradiso Amsterdam and Luxor Live, as well as at various international jazz festivals.</p>
            <?php endif; ?>
        </section>

        <?php
        $members = $viewModel->bandMembers;
        require __DIR__ . '/partials/band-members-section.php';
        ?>

        <div class="jazz-back jazz-gumbo-back">
            <a class="jazz-back-btn jazz-gumbo-back-btn" href="/jazz">&lt; BACK</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function () {
    document.querySelectorAll('.jazz-gumbo-experience-card').forEach(function (card) {
        var btn = card.querySelector('.jazz-gumbo-exp-play');
        var audio = card.querySelector('.jazz-gumbo-exp-audio');
        if (!btn || !audio) {
            return;
        }
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
                        if (pb) {
                            pb.classList.remove('is-playing');
                        }
                        if (ic) {
                            ic.textContent = '▶';
                        }
                    }
                }
            });
            if (audio.paused) {
                audio.play().catch(function () {});
                btn.classList.add('is-playing');
                if (icon) {
                    icon.textContent = '⏸';
                }
            } else {
                audio.pause();
                btn.classList.remove('is-playing');
                if (icon) {
                    icon.textContent = '▶';
                }
            }
        });
        audio.addEventListener('ended', function () {
            btn.classList.remove('is-playing');
            if (icon) {
                icon.textContent = '▶';
            }
        });
    });
})();
</script>
</body>
</html>
