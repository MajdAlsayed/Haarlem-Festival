<?php
$blocks = $viewModel->blocks;
$locations = $viewModel->locations;

$hero = $blocks['hero']['content'] ?? [];
$aboutBanner = $blocks['about_banner']['content'] ?? [];
$sitesHeader = $blocks['section_header']['content'] ?? [];
$locationCards = $blocks['location_cards']['content'] ?? [];
$experience = $blocks['text_block']['content'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hero['title'] ?? '') ?></title>
    <!-- General site CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <!-- CSS for History -->
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
                <div class="history-hero-bottom">
                    <p class="history-hero-description"><?= htmlspecialchars($hero['description'] ?? '') ?></p>
                    <a href="<?= htmlspecialchars($hero['button_url'] ?? '') ?>" class="history-button-big">
                        <span class="history-button-text"><?= htmlspecialchars($hero['button_text'] ?? '') ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- BREADCRUMB -->
    <nav class="history-breadcrumb" aria-label="Breadcrumb">
        <div class="container">
            <a href="/" class="history-breadcrumb-link">HOME</a>
            <span class="history-breadcrumb-separator">→</span>
            <span class="history-breadcrumb-link active" aria-current="page">HISTORY</span>
        </div>
    </nav>

    <!-- ABOUT BANNER -->
    <section class="history-heritage-banner">
        <div class="container">
            <h2 class="history-heritage-title"><?= htmlspecialchars($aboutBanner['title'] ?? '') ?></h2>
            <div class="history-heritage-content">
                <p class="history-heritage-text"><?= htmlspecialchars($aboutBanner['text_1'] ?? '') ?></p>
                <p class="history-heritage-text"><?= htmlspecialchars($aboutBanner['text_2'] ?? '') ?></p>
            </div>
        </div>
    </section>

    <!-- 9 SITES SECTION -->
    <section class="history-sites-section">
        <div class="container">
            <div class="history-sites-header">
                <h2 class="history-sites-title"><?= nl2br(htmlspecialchars($sitesHeader['title'] ?? '')) ?></h2>
                <p class="history-sites-description"><?= htmlspecialchars($sitesHeader['description'] ?? '') ?></p>
            </div>

            <!-- LOCATION CARDS -->
            <div class="history-cards-container">
                <?php foreach ($locations as $location): ?>
                    <?php $image = $viewModel->primaryImages[$location->id] ?? null; ?>
                    <div class="history-card">
                        <div class="history-card-image">
                            <img src="<?= htmlspecialchars($image?->imageUrl ?? '') ?>"
                                 alt="<?= htmlspecialchars($location->name) ?>">
                        </div>
                        <div class="history-card-content">
                            <div class="history-card-info">
                                <h3 class="history-card-title"><?= htmlspecialchars($location->name) ?></h3>
                                <p class="history-card-text"><?= htmlspecialchars($location->shortDescription) ?></p>
                            </div>
                            <a href="/history/location/<?= htmlspecialchars($location->slug) ?>"
                               class="history-read-more-button">
                                <span class="history-read-more-text">READ MORE ></span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- EXPLORE BUTTON -->
            <a href="<?= htmlspecialchars($locationCards['button_url'] ?? '/history/locations') ?>"
               class="history-button-big">
                <span class="history-button-text"><?= htmlspecialchars($locationCards['button_text'] ?? '') ?></span>
            </a>
        </div>
    </section>

    <!-- EXPERIENCE SECTION -->
    <section class="history-experience-section">
        <div class="container">
            <h2 class="history-experience-title"><?= htmlspecialchars($experience['title'] ?? '') ?></h2>
            <p class="history-experience-description"><?= htmlspecialchars($experience['description'] ?? '') ?></p>
            <a href="<?= htmlspecialchars($experience['button_url'] ?? '') ?>" class="history-button-big">
                <span class="history-button-text"><?= htmlspecialchars($experience['button_text'] ?? '') ?></span>
            </a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
