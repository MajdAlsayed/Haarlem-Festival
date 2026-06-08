<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$featured = $vm->featured ?? [];
$heroImages = [
    $vm->settings['hero_image_1'] ?? '/images/Stories/stories-home-image-main1.png',
    $vm->settings['hero_image_2'] ?? '/images/Stories/stories-home-image-main2.jpg',
    $vm->settings['hero_image_3'] ?? '/images/Stories/stories-home-image-main3.jpg',
    $vm->settings['hero_image_4'] ?? '/images/Stories/stories-home-image-main4.jpeg',
];

$heroImages = array_map(static function ($path): string {
    $path = trim((string)$path);
    if ($path === '') {
        return '/images/Stories/stories-home-image-main1.png';
    }

    return str_starts_with($path, '/') ? $path : '/images/Stories/' . $path;
}, $heroImages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($vm->pageTitle ?? 'Stories in Haarlem') ?></title>

    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/Stories/home-featured.css?v=<?= h($app['css_version'] ?? '1') ?>-hero4">
</head>

<body class="stories-home">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Hero banner -->
    <section class="stories-hero-banner" aria-label="Hero images">
        <?php foreach ($heroImages as $i => $heroImage): ?>
            <div class="hero-img hero-img-<?= $i + 1 ?>" style="background-image: url('<?= h($heroImage) ?>');" role="img" aria-label="Stories banner image <?= $i + 1 ?>"></div>
        <?php endforeach; ?>
        <div class="hero-overlay">
            <h1><?= h($vm->settings['home_hero_heading'] ?? 'Welcome to Stories In Haarlem') ?></h1>
            <p><?= h($vm->settings['home_hero_tagline'] ?? 'Experience Haarlem Through Stories – Past, Present & Future.') ?></p>
        </div>
    </section>

    <!-- Breadcrumb -->
    <nav class="stories-breadcrumb" aria-label="Breadcrumb">
        <div class="stories-breadcrumb-inner">
            <a href="/" class="stories-breadcrumb-link">HOME</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">→</span>
            <span class="stories-breadcrumb-link active" aria-current="page">STORIES</span>
        </div>
    </nav>

    <!-- Intro section -->
    <section class="stories-heading-section">
        <div class="stories-heading-inner">
            <h2 class="stories-heading-title"><?= h($vm->settings['home_intro_heading'] ?? 'The City That Speaks Through Its People') ?></h2>
            <p class="stories-heading-text">
                <?= h($vm->settings['home_intro_text'] ?? 'Haarlem\'s rich tradition of storytelling lives in every corner of the city — from narrow cobblestone streets to centuries-old courtyards. During Stories in Haarlem, local residents, historians, and performers bring hidden tales to life through intimate sessions that reveal the city\'s humor, heart, and heritage.') ?>
            </p>
        </div>
    </section>

    <!-- What You Can Explore Section -->
    <section class="stories-explore-section" aria-label="What you can explore">
        <div class="stories-explore-inner">
            <h2 class="stories-explore-title"><?= h($vm->settings['home_explore_title'] ?? 'What You Can Explore') ?></h2>
            <div class="stories-explore-list">
                <?php
                $exploreItems = !empty($vm->settings['home_explore_items']) ? json_decode($vm->settings['home_explore_items'], true) : [];
                if (!is_array($exploreItems)) $exploreItems = [];
                ?>
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
            <p class="stories-events-subtitle"><?= h($vm->settings['home_events_subtitle'] ?? 'From grand churches to hidden courtyards, each landmark showcases Haarlem\'s transformation from medieval town to cultural treasure.') ?></p>
        </div>
    </section>

    <!-- Featured Stories Section -->
    <section class="stories-featured" aria-label="Featured stories">
        <div class="stories-featured-header">
            <h2><?= h($vm->settings['home_featured_heading'] ?? 'Featured Stories') ?></h2>
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
                    <article class="featured-card">
                        <div class="featured-card-image">
                            <img src="<?= h($image) ?>" alt="<?= h($name) ?>">
                        </div>
                        <div class="featured-card-body">
                            <div class="featured-card-info">
                                <h3 class="featured-card-title"><?= h($name) ?></h3>
                                <?php if ($type): ?>
                                    <p class="featured-card-type"><?= h($type) ?></p>
                                <?php endif; ?>
                                <p class="featured-card-desc"><?= h(substr($desc, 0, 100)) ?>...</p>
                                <?php if ($age): ?>
                                    <div class="featured-card-age">Age <?= h($age) ?></div>
                                <?php endif; ?>
                            </div>
                            <a href="/stories/detail?id=<?= $storyId ?>" class="featured-card-link">
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
            <p class="stories-echoes-subtitle"><?= h($vm->settings['home_echoes_subtitle'] ?? 'Explore the rich narratives woven into Haarlem\'s historic streets. This guided experience introduces you to significant sites and the stories behind their importance in the city\'s history.') ?></p>
            <a href="/stories/events" class="stories-echoes-button"><?= h($vm->settings['home_echoes_button'] ?? 'View our Stories') ?></a>
        </div>
    </section>

    <!-- About Stories Section -->
    <section class="stories-about-section" aria-label="About Stories">
        <div class="stories-about-inner">
            <h2 class="stories-about-title"><?= h($vm->settings['home_about_title'] ?? 'About Stories') ?></h2>
            <p class="stories-about-text"><?= h($vm->settings['home_about_text'] ?? 'Stories in Haarlem brings together voices, memories, and imagination from across the city. Through live performances, family-friendly tales, podcasts, and historical storytelling, visitors can lose themselves in a new and meaningful way. Each event takes place in a unique venue, creating a special interaction between the storyteller, the place, and the audience. Immersive and enchanting, the Stories Invites everyone to experience the city through storytelling.') ?></p>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
