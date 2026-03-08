<?php
$hero = null;
$aboutBanner = null;
$statsBar = null;
$contentSections = [];
$experience = null;

foreach ($viewModel->blocks as $block) {
    switch ($block['block_type']) {
        case 'hero':
            $hero = $block['content'];
            break;
        case 'about_banner':
            $aboutBanner = $block['content'];
            break;
        case 'stats_bar':
            $statsBar = $block['content'];
            break;
        case 'content_section':
            $contentSections[] = $block['content'];
            break;
        case 'experience':
            $experience = $block['content'];
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hero['title'] ?? '') ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/history.css">
</head>
<body class="history-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- HERO -->
    <section class="history-hero-section">
        <div class="history-hero-background">
            <img
                    src="<?= htmlspecialchars($viewModel->heroImage?->imageUrl ?? '') ?>"
                    alt="<?= htmlspecialchars($viewModel->heroImage?->altText ?? '') ?>"
            >
        </div>
        <div class="history-hero-content">
            <div class="container">
                <div class="history-hero-title-container">
                    <h1 class="history-hero-title"><?= nl2br(htmlspecialchars($hero['title'] ?? '')) ?></h1>
                    <p class="history-hero-subtitle"><?= htmlspecialchars($hero['subtitle'] ?? '') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- BREADCRUMBS -->
    <nav class="history-breadcrumb" aria-label="Breadcrumb">
        <div class="container">
            <a href="/" class="history-breadcrumb-link">HOME</a>
            <span class="history-breadcrumb-separator">→</span>
            <a href="/history" class="history-breadcrumb-link">HISTORY</a>
            <span class="history-breadcrumb-separator">→</span>
            <a href="/history/locations" class="history-breadcrumb-link">LANDMARKS</a>
            <span class="history-breadcrumb-separator">→</span>
            <span class="history-breadcrumb-link active" aria-current="page">
                <?= htmlspecialchars($viewModel->location->name) ?>
            </span>
        </div>
    </nav>

    <!-- ABOUT BANNER -->
    <?php if ($aboutBanner): ?>
        <section class="history-heritage-banner">
            <div class="container">
                <p class="history-intro-text"><?= htmlspecialchars($aboutBanner['text'] ?? '') ?></p>
            </div>
        </section>
    <?php endif; ?>

    <!-- STATS BAR -->
    <?php if ($statsBar): ?>
        <section class="history-stats-banner">
            <div class="container">
                <?php foreach ($statsBar['stats'] as $stat): ?>
                    <div class="history-stats-item">
                        <span class="history-stats-value"><?= htmlspecialchars($stat['value']) ?></span>
                        <span class="history-stats-label"><?= htmlspecialchars($stat['label']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- CONTENT SECTIONS -->
    <?php foreach ($contentSections as $section): ?>
        <?php $layoutClass = 'history-' . $section['layout']; ?>
        <section class="history-content-section <?= $layoutClass ?>">
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
                                <p class="history-content-section-paragraph">
                                    <?= htmlspecialchars($paragraph) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Media block -->
                <div class="history-content-media">
                    <?php foreach ($section['image_ids'] ?? [] as $imageId): ?>
                        <?php $image = $viewModel->contentImages[$imageId] ?? null; ?>
                        <?php if ($image): ?>
                            <div class="history-content-image">
                                <img src="<?= htmlspecialchars($image->imageUrl) ?>"
                                     alt="<?= htmlspecialchars($image->altText) ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($section['did_you_know'])): ?>
                        <div class="history-did-you-know">
                            <p class="history-did-you-know-title">DID YOU KNOW?</p>
                            <?php foreach ($section['did_you_know'] as $fact): ?>
                                <p class="history-did-you-know-text"><?= htmlspecialchars($fact) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    <?php endforeach; ?>

    <!-- EXPERIENCE BANNER -->
    <?php if ($experience): ?>
        <section class="history-experience-banner">
            <div class="container">
                <h2 class="history-experience-banner-title">
                    <?= htmlspecialchars($experience['title'] ?? '') ?>
                </h2>
                <div class="history-experience-cards">

                    <!-- Independent Visit -->
                    <div class="history-experience-card-wrapper">
                        <h3 class="history-experience-card-title history-experience-card-title--dark">
                            <?= htmlspecialchars($experience['independent']['title'] ?? '') ?>
                        </h3>
                        <div class="history-experience-card history-experience-card--white">
                            <p class="history-experience-card-text">
                                <?= htmlspecialchars($experience['independent']['text'] ?? '') ?>
                            </p>
                            <?php if (!empty($experience['independent']['subtitle'])): ?>
                                <p class="history-experience-card-subtitle">
                                    <?= htmlspecialchars($experience['independent']['subtitle']) ?>
                                </p>
                            <?php endif; ?>
                            <?php foreach ($experience['independent']['details'] ?? [] as $detail): ?>
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
                            <?= htmlspecialchars($experience['guided']['title'] ?? '') ?>
                        </h3>
                        <div class="history-experience-card history-experience-card--orange">
                            <p class="history-experience-card-text">
                                <?= htmlspecialchars($experience['guided']['text'] ?? '') ?>
                            </p>
                            <a href="/history" class="history-button-big">
                                <span class="history-button-text">VIEW TOUR DETAILS</span>
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
                <a href="/history/location/<?= htmlspecialchars($viewModel->prevLocation->slug) ?>"
                   class="history-button-big">
                    <span class="history-button-text">< <?= htmlspecialchars($viewModel->prevLocation->name) ?></span>
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <a href="/history/locations" class="history-button-big">
                <span class="history-button-text">ALL LANDMARKS</span>
            </a>

            <?php if ($viewModel->nextLocation): ?>
                <a href="/history/location/<?= htmlspecialchars($viewModel->nextLocation->slug) ?>"
                   class="history-button-big">
                    <span class="history-button-text"><?= htmlspecialchars($viewModel->nextLocation->name) ?> ></span>
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