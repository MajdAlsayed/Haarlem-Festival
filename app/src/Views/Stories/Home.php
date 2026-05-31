<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = (new \App\Repositories\SettingsRepository())->getAll();

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$featured = $vm->featured ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($vm->pageTitle ?? 'Stories in Haarlem') ?></title>

    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/Stories/home-featured.css?v=<?= h($app['css_version'] ?? '1') ?>">
</head>

<body class="stories-home">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>

    <!-- Hero banner -->
    <section class="stories-hero-banner" aria-label="Hero images">
        <div class="hero-img" style="background-image: url('/images/Stories/hero-1.jpg');" role="img" aria-label="Stories banner image 1"></div>
        <div class="hero-img" style="background-image: url('/images/Stories/hero-2.jpg');" role="img" aria-label="Stories banner image 2"></div>
        <div class="hero-overlay">
            <h1>Welcome to Stories<br>In Haarlem</h1>
            <p>Experience Haarlem Through Stories – Past, Present &amp; Future.</p>
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
            <h2 class="stories-heading-title">The City That Speaks Through Its People</h2>
            <p class="stories-heading-text">
                Haarlem's rich tradition of storytelling lives in every corner of the city — from narrow cobblestone streets
                to centuries-old courtyards. During Stories in Haarlem, local residents, historians, and performers bring
                hidden tales to life through intimate sessions that reveal the city's humor, heart, and heritage.
            </p>
        </div>
    </section>

    <!-- Featured Stories Section -->
    <section class="stories-featured" aria-label="Featured stories">
        <div class="stories-featured-header">
            <h2>Featured Stories</h2>
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
                        <div class="featured-card-image" style="background-image: url('<?= h($image) ?>')"></div>
                        <div class="featured-card-body">
                            <h3 class="featured-card-title"><?= h($name) ?></h3>
                            <?php if ($type): ?>
                                <p class="featured-card-type"><?= h($type) ?></p>
                            <?php endif; ?>
                            <p class="featured-card-desc"><?= h(substr($desc, 0, 100)) ?>...</p>
                            <?php if ($age): ?>
                                <div class="featured-card-age">Age <?= h($age) ?></div>
                            <?php endif; ?>
                            <a href="/stories/detail?id=<?= $storyId ?>" class="featured-card-link">
                                Read More →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="stories-featured-footer">
            <a href="/stories/events" class="btn-primary">View All Events →</a>
        </div>
    </section>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
