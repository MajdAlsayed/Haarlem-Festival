<?php
//Shared festival hero for overview/index pages.

$heroModifier = $heroModifier ?? '';
$heroImage = $heroImage ?? '';
$heroImageAlt = $heroImageAlt ?? '';
$heroEyebrow = $heroEyebrow ?? 'Haarlem Festival';
$heroTitle = $heroTitle ?? '';
$heroSubtitle = $heroSubtitle ?? '';
$heroButtonText = $heroButtonText ?? '';
$heroButtonUrl = $heroButtonUrl ?? '';
$heroButtonClass = $heroButtonClass ?? 'btn btn--light';

$h = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>

<section class="festival-hero <?= $h($heroModifier) ?>">
    <?php if ($heroImage !== ''): ?>
        <div class="festival-hero__background" aria-hidden="true">
            <img src="<?= $h($heroImage) ?>" alt="<?= $h($heroImageAlt) ?>">
        </div>
    <?php endif; ?>

    <div class="container festival-hero__inner">
        <div class="festival-hero__panel">


            <?php if ($heroTitle !== ''): ?>
                <h1 class="festival-hero__title">
                    <?= $h($heroTitle) ?>
                </h1>
            <?php endif; ?>

            <?php if ($heroSubtitle !== ''): ?>
                <div class="festival-hero__subtitle cms-html">
                    <?= $heroSubtitle ?>
                </div>
            <?php endif; ?>

            <?php if ($heroButtonText !== '' && $heroButtonUrl !== ''): ?>
                <div class="festival-hero__actions">
                    <a href="<?= $h($heroButtonUrl) ?>" class="<?= $h($heroButtonClass) ?>">
                        <?= $h($heroButtonText) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>