<?php
/** @var array<string, string> $cmsHome */
$h = $cmsHome ?? [];
?>
<section class="about-section">
    <div class="container">
        <h2 class="about-heading"><?= htmlspecialchars($h['about_heading'] ?? '') ?></h2>
        <div class="about-grid">
            <div class="about-image-wrapper">
                <img src="<?= htmlspecialchars($h['about_image_src'] ?? '/images/About-haarlem.jpg') ?>" alt="<?= htmlspecialchars($h['about_image_alt'] ?? '') ?>" class="about-image">
            </div>
            <div class="about-content">
                <div class="about-text cms-html">
                    <?= \App\Core\HtmlSanitizer::purify($h['about_text'] ?? '') ?>
                </div>
                <a href="<?= htmlspecialchars($h['about_more_href'] ?? '#') ?>" class="about-link"><?= htmlspecialchars($h['about_more_label'] ?? '') ?></a>
            </div>
        </div>
    </div>
</section>
