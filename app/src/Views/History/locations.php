<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->hero['title'] ?? '') ?></title>
    <!-- General site CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <!-- CSS for History -->
    <link rel="stylesheet" href="/css/history.css">
</head>
<body class="history-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- HERO -->
    <?php
    $sectionModifier = 'history-hero-section--locations';
    $titleModifier = 'history-hero-title--yellow';
    $showSubtitle = false;
    $showButton = false;
    require __DIR__ . '/../partials/history/history-hero.php';
    ?>

    <!-- BREADCRUMBS -->
    <?php
    $breadcrumbs = [
        ['label' => 'HOME', 'url' => '/'],
        ['label' => 'HISTORY', 'url' => '/history'],
        ['label' => 'LOCATIONS', 'url' => null],
    ];
    require __DIR__ . '/../partials/history/history-breadcrumb.php';
    ?>

    <!-- ABOUT BANNER -->
    <?php
    $sectionModifier = '';
    $showTitle = false;
    $textModifier = 'history-about-banner--bold';
    require __DIR__ . '/../partials/history/history-about-banner.php';
    ?>

    <!-- LOCATION CARDS -->
    <div class="history-locations-section">
        <?php
        $index = 0;
        foreach ($viewModel->locations as $location):
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