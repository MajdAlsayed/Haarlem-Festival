<?php

// Settings for the page title, styles, body class
$pageTitle = $viewModel->hero['title'] ?? 'History — Haarlem Festival';
$pageStyles = ['/css/pages/history.css'];
$bodyClass = 'history-page';

$breadcrumbs = [
['label' => 'Home', 'url' => '/'],
['label' => 'History', 'url' => '/history'],
['label' => 'Landmarks', 'url' => '/history/locations'],
['label' => $viewModel->location->name, 'url' => null],
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="history-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- HERO -->
    <?php
    $sectionModifier = '';
    $titleModifier = '';
    $showSubtitle = true;
    $showButton = false;
    require __DIR__ . '/../partials/history/history-hero.php';
    ?>

    <!-- BREADCRUMBS -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <!-- ABOUT BANNER -->
    <?php if ($viewModel->aboutBanner): ?>
        <?php
        $sectionModifier = '';
        $showTitle = false;
        $textModifier = 'history-about-banner--bold';
        require __DIR__ . '/../partials/history/history-about-banner.php';
        ?>
    <?php endif; ?>

    <!-- STATS BAR -->
    <?php if ($viewModel->statsBar): ?>
        <section class="history-stats-banner">
            <div class="container">
                <?php foreach ($viewModel->statsBar['stats'] as $stat): ?>
                    <div class="history-stats-item">
                        <span class="section-title section-title--accent history-stats-value">
                            <?= htmlspecialchars($stat['value']) ?>
                        </span>
                        <span class="copy-text copy-text--sm history-stats-label">
                            <?= htmlspecialchars($stat['label']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- CONTENT SECTIONS -->
    <?php foreach ($viewModel->contentSections as $section): ?>
        <?php
        $layoutClass = 'history-' . ($section['layout'] ?? '');
        ?>
        <section class="history-content-section <?= htmlspecialchars($layoutClass) ?>">
            <div class="container">

                <!-- Text block -->
                <div class="history-content-text-block">
                    <h2 class="section-title section-title--accent history-content-section-title">
                        <?= htmlspecialchars($section['title'] ?? '') ?>
                    </h2>

                    <?php foreach ($section['sections'] as $subsection): ?>
                        <div class="history-content-section-item">
                            <h3 class="section-subtitle history-content-section-subtitle">
                                <?= htmlspecialchars($subsection['subtitle'] ?? '') ?>
                            </h3>

                            <?php foreach ($subsection['paragraphs'] as $paragraph): ?>
                                <div class="copy-text history-content-section-paragraph cms-html">
                                    <?= $paragraph ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- Media block -->
                <div class="history-content-media">
                    <?php foreach ($section['image_ids'] ?? [] as $imageId): ?>
                        <?php
                        $image = $viewModel->contentImages[$imageId] ?? null;
                        ?>
                        <?php if ($image): ?>
                            <div class="history-content-image">
                                <img src="<?= htmlspecialchars($image->imageUrl) ?>"
                                     alt="<?= htmlspecialchars($image->altText) ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($section['did_you_know'])): ?>
                        <div class="history-did-you-know">
                            <p class="section-subtitle history-did-you-know-title">
                                DID YOU KNOW?
                            </p>

                            <?php foreach ($section['did_you_know'] as $fact): ?>
                                <div class="copy-text history-did-you-know-text cms-html">
                                    <?= $fact ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    <?php endforeach; ?>

    <!-- EXPERIENCE BANNER -->
    <?php if ($viewModel->experience): ?>
        <section class="history-experience-banner">
            <div class="container">
                <h2 class="section-title section-title--dark history-experience-banner-title">
                    <?= htmlspecialchars($viewModel->experience['title'] ?? '') ?>
                </h2>
                <div class="history-experience-cards">

                    <!-- Independent Visit -->
                    <div class="history-experience-card-wrapper">
                        <h3 class="section-subtitle history-experience-card-title history-experience-card-title--dark">
                            <?= htmlspecialchars($viewModel->experience['independent']['title'] ?? '') ?>
                        </h3>
                        <div class="history-experience-card history-experience-card--white">
                            <div class="copy-text copy-text--dark history-experience-card-text cms-html">
                                <?= $viewModel->experience['independent']['text'] ?? '' ?>
                            </div>

                            <?php if (!empty($viewModel->experience['independent']['subtitle'])): ?>
                                <h3 class="section-subtitle history-experience-card-subtitle">
                                    <?= htmlspecialchars($viewModel->experience['independent']['subtitle']) ?>
                                </h3>
                            <?php endif; ?>

                            <?php foreach ($viewModel->experience['independent']['details'] ?? [] as $detail): ?>
                                <p class="copy-text copy-text--dark copy-text--sm history-experience-card-detail">
                                    <span class="history-experience-card-detail-label">
                                        <?= htmlspecialchars($detail['label']) ?>
                                    </span>
                                    <?= htmlspecialchars($detail['text']) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Guided Tour -->
                    <div class="history-experience-card-wrapper">
                        <h3 class="section-subtitle history-experience-card-title history-experience-card-title--dark">
                            <?= htmlspecialchars($viewModel->experience['guided']['title'] ?? '') ?>
                        </h3>
                        <div class="history-experience-card history-experience-card--accent">
                            <div class="copy-text history-experience-card-text cms-html">
                                <?= $viewModel->experience['guided']['text'] ?? '' ?>
                            </div>
                            <a href="/history/tours" class="btn btn--light btn--fit btn--light-on-accent history-experience-card-button">
                                <span class="history-button-text">
                                    VIEW TOUR DETAILS
                                </span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- LOCATION NAVIGATION -->
    <nav class="history-location-nav" aria-label="Location navigation">
        <div class="container">

            <?php if ($viewModel->prevLocation): ?>
                <a href="/history/location/<?= htmlspecialchars($viewModel->prevLocation->slug) ?>" class="btn btn--outline">
                    ← Previous
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <a href="/history/locations" class="btn btn--outline">
                <span class="history-button-text">
                    All Locations
                </span>
            </a>

            <?php if ($viewModel->nextLocation): ?>
                <a href="/history/location/<?= htmlspecialchars($viewModel->nextLocation->slug) ?>" class="btn btn--outline">
                    Next →
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
        </div>
    </nav>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>