<?php
/** @var array<string, string> $cmsHome */
$h = $cmsHome ?? [];
?>
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">
            <?= htmlspecialchars($h['hero_heading'] ?? '') ?>
        </h1>
        <div class="hero-lead hero-subtitle cms-html">
            <?= \App\Core\HtmlSanitizer::purify($h['hero_subtitle'] ?? '') ?>
        </div>
        <a class="btn btn--light" href="<?= htmlspecialchars($h['hero_cta_href'] ?? '#events') ?>">
            <?= htmlspecialchars($h['hero_cta_label'] ?? '') ?>
        </a>
    </div>
</section>
