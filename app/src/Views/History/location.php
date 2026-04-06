<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->hero['title'] ?? '') ?></title>

    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/history.css">
</head>
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
    <?php
    $breadcrumbs = [
        ['label' => 'HOME', 'url' => '/'],
        ['label' => 'HISTORY', 'url' => '/history'],
        ['label' => 'LOCATIONS', 'url' => '/history/locations'],
        ['label' => strtoupper($viewModel->location->name), 'url' => null],
    ];
    require __DIR__ . '/../partials/history/history-breadcrumb.php';
    ?>

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
                        <span class="history-stats-value">
                            <?= htmlspecialchars($stat['value']) ?>
                        </span>
                        <span class="history-stats-label">
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
                    <h2 class="history-content-section-title">
                        <?= htmlspecialchars($section['title'] ?? '') ?>
                    </h2>

                    <?php foreach ($section['sections'] as $subsection): ?>
                        <div class="history-content-section-item">
                            <p class="history-content-section-subtitle">
                                <?= htmlspecialchars($subsection['subtitle'] ?? '') ?>
                            </p>

                            <?php foreach ($subsection['paragraphs'] as $paragraph): ?>
                                <div class="history-content-section-paragraph cms-html">
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
                            <p class="history-did-you-know-title">
                                DID YOU KNOW?
                            </p>

                            <?php foreach ($section['did_you_know'] as $fact): ?>
                                <div class="history-did-you-know-text cms-html">
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
                <h2 class="history-experience-banner-title">
                    <?= htmlspecialchars($viewModel->experience['title'] ?? '') ?>
                </h2>
                <div class="history-experience-cards">

                    <!-- Independent Visit -->
                    <div class="history-experience-card-wrapper">
                        <h3 class="history-experience-card-title history-experience-card-title--dark">
                            <?= htmlspecialchars($viewModel->experience['independent']['title'] ?? '') ?>
                        </h3>
                        <div class="history-experience-card history-experience-card--white">
                            <div class="history-experience-card-text cms-html">
                                <?= $viewModel->experience['independent']['text'] ?? '' ?>
                            </div>

                            <?php if (!empty($viewModel->experience['independent']['subtitle'])): ?>
                                <p class="history-experience-card-subtitle">
                                    <?= htmlspecialchars($viewModel->experience['independent']['subtitle']) ?>
                                </p>
                            <?php endif; ?>

                            <?php foreach ($viewModel->experience['independent']['details'] ?? [] as $detail): ?>
                                <p class="history-experience-card-detail">
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
                        <h3 class="history-experience-card-title history-experience-card-title--dark">
                            <?= htmlspecialchars($viewModel->experience['guided']['title'] ?? '') ?>
                        </h3>
                        <div class="history-experience-card history-experience-card--orange">
                            <div class="history-experience-card-text cms-html">
                                <?= $viewModel->experience['guided']['text'] ?? '' ?>
                            </div>
                            <a href="/history/tours" class="history-button-big">
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
                <a href="/history/location/<?= htmlspecialchars($viewModel->prevLocation->slug) ?>" class="history-button-big">
                    <span class="history-button-text">
                        < <?= htmlspecialchars($viewModel->prevLocation->name) ?>
                    </span>
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <a href="/history/locations" class="history-button-big">
                <span class="history-button-text">
                    ALL LANDMARKS
                </span>
            </a>

            <?php if ($viewModel->nextLocation): ?>
                <a href="/history/location/<?= htmlspecialchars($viewModel->nextLocation->slug) ?>" class="history-button-big">
                    <span class="history-button-text">
                        <?= htmlspecialchars($viewModel->nextLocation->name) ?> >
                    </span>
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