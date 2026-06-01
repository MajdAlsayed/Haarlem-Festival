<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$featured = $vm->featured ?? [];

// Settings for the page title, styles, body class
$pageTitle = $vm->pageTitle ?? 'Stories in Haarlem';
$pageStyles = ['/css/pages/stories.css'];
$bodyClass = 'stories-page';

// Hero settings
$heroModifier = 'festival-hero--stories';
$heroImage = '/images/Stories/stories-home-hero.jpg';
$heroImageAlt = 'Stories in Haarlem';
$heroTitle = "Welcome to Stories In Haarlem";
$heroSubtitle = 'Experience Haarlem Through Stories – Past, Present & Future';

// Breadcrumbs
$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Stories', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>

<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Hero banner -->
    <?php require __DIR__ . '/../partials/festival-hero.php'; ?>

    <!-- Breadcrumb -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <!-- Intro section -->
    <section class="stories-about-banner">
        <div class="container">
            <div class="stories-about-banner-content">
                <h2 class="section-title section-title--accent section-title--underlined stories-about-banner-title">
                    The City That Speaks Through Its People
                </h2>

                <p class="copy-text">
                    Haarlem's rich tradition of storytelling lives in every corner of the city — from narrow cobblestone streets
                    to centuries-old courtyards. During Stories in Haarlem, local residents, historians, and performers bring
                    hidden tales to life through intimate sessions that reveal the city's humor, heart, and heritage.
                </p>
            </div>
        </div>
    </section>

    <!-- Featured Stories Section -->
    <section class="container stories-featured" aria-label="Featured stories">
        <div class="stories-featured-header">
            <h2 class="section-title section-title--accent section-title--underlined" >
                Featured Stories
            </h2>
        </div>

        <div class="stories-featured-grid">
            <?php if (empty($featured)): ?>
                <p class="stories-empty">No stories available at this time.</p>
            <?php else: ?>
                <?php foreach ($featured as $story): ?>
                    <?php
                    $storyId = (int)($story['story_id'] ?? 0);
                    $image = $story['image_path'] ?? '/images/Stories/cards/default.jpg';
                    $name = $story['story_name'] ?? $story['name'] ?? '';
                    $desc = $story['description'] ?? '';
                    $type = $story['story_type'] ?? '';
                    $age = $story['age'] ?? '';
                    ?>
                    <article class="festival-card festival-card--stories story-card">
                        <div class="story-card-image-wrap">
                            <img src="<?= h($image) ?>" alt="<?= h($name) ?>" class="story-card-image">
                        </div>
                        <div class="story-card-body">
                            <h3 class="story-card-title"><?= h($name) ?></h3>
                            <?php if ($type): ?>
                                <p class="copy-text copy-text--sm story-card-type"><?= h($type) ?></p>
                            <?php endif; ?>
                            <p class="copy-text copy-text--sm story-card-desc"><?= h(substr($desc, 0, 100)) ?>...</p>
                            <?php if ($age): ?>
                                <span class="story-card-age">Age <?= h($age) ?></span>
                            <?php endif; ?>
                            <a href="/stories/detail?id=<?= $storyId ?>" class="btn btn--sm btn--primary story-card-link">
                                Read More →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="stories-featured-footer">
            <a href="/stories/events" class="btn btn--sm btn--outline">VIEW ALL EVENTS →</a>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
