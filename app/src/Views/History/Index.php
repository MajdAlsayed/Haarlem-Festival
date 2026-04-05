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
    $sectionModifier = '';
    $titleModifier = '';
    $showSubtitle = true;
    $showButton = true;
    require __DIR__ . '/../partials/history/history-hero.php';
    ?>

    <!-- BREADCRUMB -->
    <?php
    $breadcrumbs = [
        ['label' => 'HOME', 'url' => '/'],
        ['label' => 'HISTORY', 'url' => null],
    ];
    require __DIR__ . '/../partials/history/history-breadcrumb.php';
    ?>

    <!-- ABOUT BANNER -->
    <?php
    $sectionModifier = '';
    $showTitle = true;
    $textModifier = 'history-about-banner-text--columns';
    require __DIR__ . '/../partials/history/history-about-banner.php';
    ?>

    <!-- 9 SITES SECTION -->
    <section class="history-sites-section">
        <div class="container">
            <div class="history-sites-header">
                <h2 class="history-sites-title"><?= nl2br(htmlspecialchars($viewModel->sitesHeader['title'] ?? '')) ?></h2>
                <p class="history-sites-description"><?= htmlspecialchars($viewModel->sitesHeader['description'] ?? '') ?></p>
            </div>

            <!-- LOCATION CARDS -->
            <div class="history-cards-container">
                <?php foreach ($viewModel->locations as $location): ?>
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
            <a href="<?= htmlspecialchars($viewModel->locationCards['button_url'] ?? '/history/locations') ?>"
               class="history-button-big">
                <span class="history-button-text"><?= htmlspecialchars($viewModel->locationCards['button_text'] ?? '') ?></span>
            </a>
        </div>
    </section>

    <!-- EXPERIENCE SECTION -->
    <section class="history-experience-section">
        <div class="container">
            <h2 class="history-experience-title"><?= htmlspecialchars($viewModel->experience['title'] ?? '') ?></h2>
            <p class="history-experience-description"><?= htmlspecialchars($viewModel->experience['description'] ?? '') ?></p>
            <a href="<?= htmlspecialchars($viewModel->experience['button_url'] ?? '') ?>" class="history-button-big">
                <span class="history-button-text"><?= htmlspecialchars($viewModel->experience['button_text'] ?? '') ?></span>
            </a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
