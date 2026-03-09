<?php
$blocks = $viewModel->blocks;
$locations = $viewModel->locations;

$hero = $blocks['hero']['content'] ?? [];
$aboutBanner = $blocks['about_banner']['content'] ?? [];
$locationCards = $blocks['location_cards']['content'] ?? [];
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
    <section class="history-hero-section history-hero-section--locations">
        <div class="history-hero-background">
            <img
                    src="<?= htmlspecialchars($viewModel->heroImage?->imageUrl ?? '') ?>"
                    alt="<?= htmlspecialchars($viewModel->heroImage?->altText ?? '') ?>"
            >
        </div>
        <div class="history-hero-content">
            <div class="container">
                <div class="history-hero-title-container">
                    <h1 class="history-hero-title history-hero-title--yellow"><?= nl2br(htmlspecialchars($hero['title'] ?? '')) ?></h1>
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
            <span class="history-breadcrumb-link active" aria-current="page">LANDMARKS</span>
        </div>
    </nav>

    <!-- ABOUT BANNER -->
    <section class="history-heritage-banner">
        <div class="container">
            <p class="history-intro-text"><?= htmlspecialchars($aboutBanner['text'] ?? '') ?></p>
        </div>
    </section>

    <!-- LOCATION CARDS -->
    <div class="history-locations-section">
        <?php
        $index = 0;
        foreach ($locations as $location):
            $isEven = $index % 2 === 0;
            $layoutClass = $isEven ? 'history-location-image-left' : 'history-location-image-right';
            $image = $viewModel->primaryImages[$location->id] ?? null;
            $index++;
            ?>
            <section class="history-location-section <?= $layoutClass ?>">
                <div class="container">
                    <div class="history-location-image-container">
                        <img
                                src="<?= htmlspecialchars($image?->imageUrl ?? '') ?>"
                                alt="<?= htmlspecialchars($location->name) ?>"
                        >
                    </div>
                    <div class="history-location-content">
                        <h2 class="history-location-title"><?= htmlspecialchars($location->name) ?></h2>
                        <div class="history-location-description">
                            <p class="history-location-text"><?= htmlspecialchars($location->description1 ?? '') ?></p>
                            <p class="history-location-text"><?= htmlspecialchars($location->description2 ?? '') ?></p>
                        </div>
                        <a href="/history/location/<?= htmlspecialchars($location->slug) ?>"
                           class="history-read-more-button">
                            <span class="history-read-more-text">READ MORE</span>
                        </a>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- GO BACK -->
    <section class="history-back-section">
        <div class="container">
            <p class="history-back-text">Go back to the Event Page</p>
            <a href="/history" class="history-button-big">
                <span class="history-button-text">BACK</span>
            </a>
        </div>
    </section>

</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>