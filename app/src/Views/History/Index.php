<?php
// Settings for the page title, styles, body class
$pageTitle = $viewModel->hero['title'] ?? 'History — Haarlem Festival';
$pageStyles = ['/css/pages/history.css'];
$bodyClass = 'history-page';

// Hero settings
$heroModifier = 'festival-hero--history';
$heroImage = $viewModel->heroImage?->imageUrl ?? '';
$heroImageAlt = $viewModel->heroImage?->altText ?? 'History';
$heroTitle = $viewModel->hero['title'] ?? 'History';
$heroSubtitle = $viewModel->hero['subtitle'] ?? '';
$heroButtonText = $viewModel->hero['button_text'] ?? '';
$heroButtonUrl = $viewModel->hero['button_url'] ?? '';
$heroButtonClass = 'btn btn--light';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'History', 'url' => null],
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- HERO -->
    <?php require __DIR__ . '/../partials/festival-hero.php'; ?>

    <!-- BREADCRUMB -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

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
                <h2 class="section-title section-title--underlined history-sites-title">
                    <?= nl2br(htmlspecialchars($viewModel->sitesHeader['title'] ?? '')) ?>
                </h2>
                <div class="section-lead history-sites-description cms-html">
                    <?= $viewModel->sitesHeader['description'] ?? '' ?>
                </div>
            </div>

            <!-- LOCATION CARDS -->
            <div class="history-cards-container">
                <?php foreach ($viewModel->locations as $location): ?>
                    <?php
                    $image = $viewModel->primaryImages[$location->id] ?? null;
                    ?>
                    <div class="festival-card festival-card--history history-card">
                        <div class="history-card-image">
                            <img src="<?= htmlspecialchars($image?->imageUrl ?? '') ?>"
                                 alt="<?= htmlspecialchars($location->name) ?>">
                        </div>
                        <div class="history-card-content">
                            <div class="history-card-info">
                                <h3 class="history-card-title">
                                    <?= htmlspecialchars($location->name) ?>
                                </h3>
                                <p class="copy-text copy-text--sm history-card-text">
                                    <?= htmlspecialchars($location->shortDescription) ?>
                                </p>
                            </div>
                            <a href="/history/location/<?= htmlspecialchars($location->slug) ?>" class="btn btn--outline btn--sm history-read-more-button">
                                READ MORE →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- EXPLORE BUTTON -->
            <a href="<?= htmlspecialchars($viewModel->locationCards['button_url'] ?? '/history/locations') ?>" class="btn btn--primary btn--sm history-section-button">
                <?= htmlspecialchars($viewModel->locationCards['button_text'] ?? '') ?>
            </a>
        </div>
    </section>

    <!-- EXPERIENCE SECTION -->
    <section class="history-experience-section">
        <div class="container">
            <h2 class="section-title section-title--underlined history-experience-title">
                <?= htmlspecialchars($viewModel->experience['title'] ?? '') ?>
            </h2>
            <div class="section-lead section-lead--center history-experience-description cms-html">
                <?= $viewModel->experience['description'] ?? '' ?>
            </div>
            <a href="<?= htmlspecialchars($viewModel->experience['button_url'] ?? '') ?>" class="btn btn--primary btn--sm history-section-button">
                <?= htmlspecialchars($viewModel->experience['button_text'] ?? '') ?>
            </a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
