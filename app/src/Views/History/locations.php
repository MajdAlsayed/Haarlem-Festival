<?php
// Settings for the page title, styles, body class
$pageTitle = $viewModel->hero['title'] ?? 'History — Haarlem Festival';
$pageStyles = ['/css/pages/history.css'];
$bodyClass = 'history-page';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'History', 'url' => '/history'],
        ['label' => 'Landmarks', 'url' => null],
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

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
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

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
        ?>
        <?php foreach ($viewModel->locations as $location): ?>
            <?php $isEven = $index % 2 === 0;
            $layoutClass = $isEven ? 'history-location-image-left' : 'history-location-image-right';
            $image = $viewModel->primaryImages[$location->id] ?? null;
            $index++;
            ?>
            <section class="history-location-section <?= htmlspecialchars($layoutClass) ?>">
                <div class="container">
                    <div class="history-location-image-container">
                        <img
                                src="<?= htmlspecialchars($image?->imageUrl ?? '') ?>"
                                alt="<?= htmlspecialchars($location->name) ?>"
                        >
                    </div>
                    <div class="history-location-content">
                        <h2 class="section-title section-title--underlined history-location-title">
                            <?= htmlspecialchars($location->name) ?>
                        </h2>
                        <div class="history-location-description">
                            <div class="copy-text history-location-text">
                                <?= htmlspecialchars($location->description1 ?? '') ?>
                            </div>
                            <div class="copy-text history-location-text">
                                <?= htmlspecialchars($location->description2 ?? '') ?>
                            </div>
                        </div>
                        <a href="/history/location/<?= htmlspecialchars($location->slug) ?>"
                           class="btn btn--outline btn--sm history-read-more-button">
                            READ MORE →
                        </a>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- GO BACK -->
    <section class="history-back-section">
        <div class="container">
            <p class="section-lead history-back-text">
                Go back to the Event Page
            </p>
            <a href="/history" class="btn btn--primary">
                ← BACK
            </a>
        </div>
    </section>

</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>