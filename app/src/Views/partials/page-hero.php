<?php
/**
 * Shared hero for detail/content pages
 * @var string $pageHeroTitle
 * @var string $pageHeroSubtitle
 * @var string $pageHeroImage
 * @var string $pageHeroAlt
 * @var string $pageHeroClass
 * @var string $pageHeroContentClass
 */

$pageHeroTitle = $pageHeroTitle ?? '';
$pageHeroSubtitle = $pageHeroSubtitle ?? '';
$pageHeroImage = $pageHeroImage ?? '';
$pageHeroAlt = $pageHeroAlt ?? $pageHeroTitle;
$pageHeroClass = trim($pageHeroClass ?? '');
$pageHeroContentClass = trim($pageHeroContentClass ?? '');

$h = $h ?? fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>

<section class="page-hero <?= $h($pageHeroClass) ?>" aria-label="<?= $h($pageHeroTitle) ?> hero">
    <div class="page-hero__background">
        <img
            src="<?= $h($pageHeroImage) ?>"
            alt="<?= $h($pageHeroAlt) ?>"
        >
    </div>

    <div class="container page-hero__inner">
        <div class="page-hero__content <?= $h($pageHeroContentClass) ?>">
            <h1 class="page-hero__title">
                <?= $h($pageHeroTitle) ?>
            </h1>

            <?php if (trim((string)$pageHeroSubtitle) !== ''): ?>
                <p class="page-hero__subtitle">
                    <?= $h($pageHeroSubtitle) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>