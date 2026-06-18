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

// Settings for the page title, styles, body class
$pageTitle = $viewModel->artistTitle . ' — Jazz — Haarlem Festival';
$pageStyles = ['/css/pages/jazz.css'];
$bodyClass = 'jazz-page jazz-artist-page jazz-gumbo-page';

//Hero settings
$pageHeroTitle = $viewModel->artistTitle;
$pageHeroSubtitle = $viewModel->tagline;
$pageHeroImage = $viewModel->heroImage;
$pageHeroAlt = $viewModel->artistTitle;
$pageHeroClass = 'jazz-detail-hero jazz-gumbo-detail-hero';
$pageHeroContentClass = 'jazz-detail-hero__content jazz-gumbo-detail-hero__content';

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

    <!-- Hero -->
    <?php require __DIR__ . '/../partials/page-hero.php'; ?>

    <!-- Breadcrumbs nav -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <section class="container jazz-artist-body">
        <!-- Gumbo Kings introduction (prototype: left-aligned heading, justified paragraph) -->
        <article class="jazz-gumbo-intro">
            <h2 class="section-title section-title--accent section-title--underlined">Gumbo Kings</h2>
            <?php if (trim($viewModel->pageIntroText) !== ''): ?>
            <p class="copy-text jazz-artist-bio"><?= nl2br($h(trim($viewModel->pageIntroText))) ?></p>
            <?php else: ?>
            <p class="copy-text jazz-artist-bio">
                Gumbo Kings is a dynamic five-piece band from the Netherlands, known for their modern yet soulful interpretation of Rhythm 'n Blues and New Orleans Groove. Inspired by legends of Memphis Soul and Delta Blues, they deliver an energetic mix of raw sound, smooth melodies, and timeless musical vibes. With their powerful stage presence and engaging performances, the Gumbo Kings have become a festival favorite, breathing life into jazz traditions with a contemporary twist.
            </p>
            <?php endif; ?>
        </article>

        <!-- Discography row + full schedule (with add-to-cart on paid slots) -->
        <section class="jazz-plan-section jazz-gumbo-plan">
            <h3 class="jazz-artist-subtitle section-subtitle section-title--underlined">Plan Your Gumbo Kings Experience</h3>

            <?php
            $discCoverAlternates = [$gumboCardImage, $viewModel->heroImage];
            ?>
            <?php if ($discography !== []): ?>
            <div class="jazz-gumbo-experience-outer jazz-gumbo-discography-outer">
            <div class="jazz-gumbo-cards jazz-gumbo-experience-grid">
                <?php require __DIR__ . '/partials/discography-experience-cards.php'; ?>
            </div>
            </div>
            <?php endif; ?>

            <div class="festival-table-wrap jazz-artist-schedule" aria-label="Event schedule from Jazz admin">
                <table class="festival-table">
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
                                    <td colspan="6" class="festival-table-empty">No schedule rows found for this artist.</td>
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
                                        <td><?= $h(strtoupper($dateLabel)) ?></td>
                                        <td><?= $h($tr) ?></td>
                                        <td><?= $h($loc) ?></td>
                                        <td><?= $p > 0 ? '€ ' . $h(number_format($p, 2)) : 'Free' ?></td>
                                        <td><?= $seats !== null ? $h($seats) : '—' ?></td>
                                        <td>
                                            <?php if ($p > 0): ?>
                                                <?php
                                                $ticketDetailsId = (int) ($e['ticket_details_id'] ?? 0);
                                                $returnUrl = $jazzCartReturnUrl;
                                                $buttonClass = 'btn btn--light btn--xs';
                                                $buttonLabel = 'Add to program';
                                                require __DIR__ . '/partials/jazz-add-to-cart-form.php';
                                                ?>
                                            <?php else: ?>
                                                <span class="festival-table-empty">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
            </div>
        </section>

        <!-- Career Highlights -->
        <section class="jazz-highlights jazz-gumbo-highlights">
            <h3 class="section-title section-title--accent section-title--underlined">Career Highlights</h3>
            <?php if (trim($viewModel->careerHighlightsHtml) !== ''): ?>
                <div class="jazz-gumbo-highlights-cms">
                    <?= \App\Services\JazzArtistHtmlSanitizer::purifyHighlights($viewModel->careerHighlightsHtml) ?>
                </div>
            <?php else: ?>
            <p class="copy-text jazz-highlight-text"><strong class="text-accent">2019</strong> — Earned the title of "most booked band" at Popronde, the Netherlands' leading traveling music festival. This distinction highlighted their growing momentum and broad appeal to audiences in cities across the country.</p>
            <p class="copy-text jazz-highlight-text"><strong class="text-accent">2020</strong> — Delivered a milestone performance on the prestigious NPO Soul & Jazz stage at Noorderslag. This appearance at one of the biggest Dutch showcase events cemented their status as a standout talent within the jazz and soul community.</p>
            <p class="copy-text jazz-highlight-text"><strong class="text-accent">2021</strong> — Released the acclaimed single "Hurtin'," a track that continued their signature fusion of modern Rhythm 'n Blues with the vintage sounds of New Orleans and Memphis.</p>
            <p class="copy-text jazz-highlight-text"><strong class="text-accent">2022</strong> — Unveiled their debut album, In The Dark, produced by Paul Willemsen. The record was praised for its authentic yet modern vibe, featuring a dynamic range of soulful ballads and high-octane tracks.</p>
            <p class="copy-text jazz-highlight-text"><strong class="text-accent">Live Performances</strong> — Renowned for their high-voltage stage energy, the Gumbo Kings have thrilled crowds at iconic venues such as Paradiso Amsterdam and Luxor Live, as well as at various international jazz festivals.</p>
            <?php endif; ?>
        </section>

        <?php
        $members = $viewModel->bandMembers;
        require __DIR__ . '/partials/band-members-section.php';
        ?>

        <div class="jazz-back">
            <a href="/jazz" class="btn btn--primary">← BACK</a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<?php require __DIR__ . '/partials/jazz-media-card-player-script.php'; ?>
</body>
</html>
