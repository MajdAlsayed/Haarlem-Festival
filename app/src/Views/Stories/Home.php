<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = $vm->appSettings;

if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$featured = $vm->featured ?? [];
$heroImages = $vm->getHomeHeroImages();
$exploreItems = $vm->getHomeExploreItems();

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
                    <?= h($vm->settings['home_intro_heading'] ?? 'The City That Speaks Through Its People') ?>
                </h2>
                <p class="copy-text">
                    <?= h($vm->settings['home_intro_text'] ?? '') ?>
                </p>
            </div>
        </div>
    </section>

    <!-- What You Can Explore Section -->
    <section class="stories-explore-section" aria-label="What you can explore">
        <div class="stories-explore-inner">
            <h2 class="stories-explore-title"><?= h($vm->settings['home_explore_title'] ?? 'What You Can Explore') ?></h2>
            <div class="stories-explore-list">
                <?php foreach ($exploreItems as $item): ?>
                    <div class="explore-item">
                        <h3><?= h($item['title'] ?? '') ?></h3>
                        <p><?= h($item['description'] ?? '') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section class="stories-events-section" aria-label="Events that tell Haarlem's story">
        <div class="stories-events-inner">
            <h2 class="stories-events-title"><?= h($vm->settings['home_events_title'] ?? '15 Events That Tell Haarlem\'s Story') ?></h2>
            <p class="stories-events-subtitle"><?= h($vm->settings['home_events_subtitle'] ?? '') ?></p>
        </div>
    </section>

    <!-- Featured Stories Section -->
    <section class="container stories-featured" aria-label="Featured stories">
        <div class="stories-featured-header">
            <h2 class="section-title section-title--accent section-title--underlined" ><?= h($vm->settings['home_featured_heading'] ?? 'Featured Stories') ?></h2>
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
                            <div class="featured-card-info">
                                <h3 class="story-card-title"><?= h($name) ?></h3>
                                <?php if ($type): ?>
                                    <p class="copy-text copy-text--sm story-card-type"><?= h($type) ?></p>
                                <?php endif; ?>
                                <p class="copy-text copy-text--sm story-card-desc"><?= h(substr($desc, 0, 100)) ?>...</p>
                                <?php if ($age): ?>
                                    <div class="story-card-age">Age <?= h($age) ?></div>
                                <?php endif; ?>
                            </div>
                            <a href="/stories/detail?id=<?= $storyId ?>" class="btn btn--sm btn--primary story-card-link">
                                Read More →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Echoes of History Section -->
    <section class="stories-echoes-section" aria-label="Echoes of history stories">
        <div class="stories-echoes-inner">
            <h2 class="stories-echoes-title"><?= h($vm->settings['home_echoes_title'] ?? 'Echoes of History: Stories of') ?></h2>
            <p class="stories-echoes-subtitle"><?= h($vm->settings['home_echoes_subtitle'] ?? '') ?></p>
            <a href="/stories/events" class="stories-echoes-button"><?= h($vm->settings['home_echoes_button'] ?? 'View our Stories') ?></a>
        </div>
    </section>

    <!-- About Stories Section -->
    <section class="stories-about-section" aria-label="About Stories">
        <div class="stories-about-inner">
            <h2 class="stories-about-title"><?= h($vm->settings['home_about_title'] ?? 'About Stories') ?></h2>
            <p class="stories-about-text"><?= h($vm->settings['home_about_text'] ?? '') ?></p>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
