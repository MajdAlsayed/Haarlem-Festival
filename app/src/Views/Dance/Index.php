<?php
/** Public Dance index. All data is prepared in DanceService; this template only displays it. */
/** @var \App\ViewModels\DanceViewModel $vm */
$appSettings = $vm->appSettings;
$breadcrumbs = $vm->breadcrumbs;

$pageTitle = $vm->pageTitle;
$pageStyles = ['/css/pages/dance.css'];
$bodyClass = 'dance-page';

// hero values the festival-hero partial reads
$heroModifier = 'festival-hero--dance';
$heroTitle = $vm->heroTitle;
$heroImageAlt = $vm->heroTitle;
$heroImage = $vm->heroImage;
$heroSubtitle = $vm->heroSubtitle;
$heroButtonText = $vm->heroButtonText;
$heroButtonUrl = $vm->heroButtonUrl;
$heroButtonClass = 'btn btn--light';
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

    <section class="dance-about container">
        <h2 class="section-title section-title--accent section-title--underlined"><?= htmlspecialchars($vm->aboutHeading) ?></h2>
        <div class="copy-text dance-about-content">
            <?php foreach ($vm->aboutParagraphs as $para): ?>
                <?php if (is_string($para) && !\App\Core\HtmlSanitizer::isEmptyHtml($para)): ?>
                    <div class="cms-html"><?= \App\Core\HtmlSanitizer::purify($para) ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="featured-events" class="dance-featured container">
        <h2 class="section-title section-title--underlined dance-section-title"><?= htmlspecialchars($vm->featuredTitle) ?></h2>
        <div class="dance-cards dance-cards-featured">
            <?php foreach ($vm->featuredCards as $card): ?>
                <a href="/dance/event/<?= (int) $card['id'] ?>" class="festival-card festival-card--dance-event dance-card u-plain-link">
                    <?php if ($card['imagePath'] !== ''): ?>
                    <div class="dance-card-image-wrap">
                        <img src="<?= htmlspecialchars($card['imagePath']) ?>" alt="<?= htmlspecialchars($card['title']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="dance-card-placeholder is-hidden">&#128247;</div>
                    </div>
                    <?php endif; ?>
                    <div class="dance-card-body">
                        <p class="copy-text copy-text--sm dance-card-meta"><span class="dance-meta-icon">&#128197;</span> <?= htmlspecialchars($card['timeLine']) ?></p>
                        <p class="copy-text copy-text--sm dance-card-meta"><span class="dance-meta-icon">&#128205;</span> <?= htmlspecialchars($card['venue']) ?></p>
                        <h3 class="dance-card-title"><?= htmlspecialchars($card['title']) ?></h3>
                        <p class="copy-text copy-text--sm dance-card-desc"><?= htmlspecialchars($card['description']) ?></p>
                        <span class="dance-genre-badge"><?= htmlspecialchars($card['genre']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="dance-all container">
        <h2 class="section-title section-title--underlined dance-section-title"><?= htmlspecialchars($vm->allEventsTitle) ?></h2>
        <div class="filter-tabs dance-date-filters" role="tablist">
            <button type="button" class="filter-tab active" data-filter="friday" aria-pressed="true"><?= htmlspecialchars($vm->dayLabels['friday']) ?></button>
            <button type="button" class="filter-tab" data-filter="saturday" aria-pressed="false"><?= htmlspecialchars($vm->dayLabels['saturday']) ?></button>
            <button type="button" class="filter-tab" data-filter="sunday" aria-pressed="false"><?= htmlspecialchars($vm->dayLabels['sunday']) ?></button>
        </div>

        <?php foreach ($vm->dayPanels as $day => $panel): ?>
        <div class="<?= $panel['panelClass'] ?>" data-filter="<?= htmlspecialchars($day) ?>" role="tabpanel">
            <div class="dance-cards dance-cards-grid">
                <?php foreach ($panel['cards'] as $card): ?>
                    <a href="/dance/event/<?= (int) $card['id'] ?>" class="festival-card festival-card--dance-event dance-card dance-card-vertical u-plain-link">
                        <?php if ($card['imagePath'] !== ''): ?>
                        <div class="dance-card-image-wrap">
                            <img src="<?= htmlspecialchars($card['imagePath']) ?>" alt="<?= htmlspecialchars($card['title']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="dance-card-placeholder is-hidden">&#128247;</div>
                        </div>
                        <?php endif; ?>
                        <div class="dance-card-body dance-card-body-stack">
                            <p class="copy-text copy-text--sm dance-card-venue"><?= htmlspecialchars($card['venueName']) ?>, <?= htmlspecialchars($card['venueCity']) ?></p>
                            <h3 class="dance-card-title"><?= htmlspecialchars($card['title']) ?></h3>
                            <p class="copy-text copy-text--sm dance-card-datetime"><?= htmlspecialchars($card['dateTime']) ?></p>
                            <p class="copy-text copy-text--sm dance-card-desc"><?= htmlspecialchars($card['description']) ?></p>
                            <span class="dance-genre-badge"><?= htmlspecialchars($card['genre']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <section id="dance-artists" class="dance-artists container">
        <h2 class="section-title section-title--underlined dance-section-title"><?= htmlspecialchars($vm->artistsTitle) ?></h2>
        <div class="dance-artists-grid">
            <?php foreach ($vm->artistCards as $artist): ?>
            <article class="festival-card festival-card--dance-artist dance-artist-card">
                <?php if ($artist['imagePath'] !== ''): ?>
                <div class="dance-artist-card-image-wrap">
                    <img src="<?= htmlspecialchars($artist['imagePath']) ?>" alt="<?= htmlspecialchars($artist['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="dance-artist-card-placeholder is-hidden">&#127908;</div>
                </div>
                <?php endif; ?>
                <div class="dance-artist-card-body">
                    <h3 class="section-subtitle dance-artist-name"><?= htmlspecialchars($artist['name']) ?></h3>
                    <p class="copy-text copy-text--sm dance-artist-bio"><?= htmlspecialchars($artist['bio']) ?></p>
                    <a href="<?= htmlspecialchars($artist['url']) ?>" class="btn btn--outline btn--sm dance-artist-info-link"><?= htmlspecialchars($vm->artistInfoLabel) ?></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn--outline dance-show-more"><?= htmlspecialchars($vm->showMoreLabel) ?></button>
    </section>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
(function() {
    var filters = document.querySelectorAll('.filter-tab');
    var panels = document.querySelectorAll('.dance-events-panel');
    filters.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.getAttribute('data-filter');
            filters.forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            // Show only the selected day panel while keeping markup static.
            panels.forEach(function(p) {
                if (p.getAttribute('data-filter') === filter) {
                    p.classList.add('active');
                } else {
                    p.classList.remove('active');
                }
            });
        });
    });
})();
</script>
</body>
</html>
