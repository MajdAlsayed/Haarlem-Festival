<?php
/** @var array<string, string> $cmsHome */
$h = $cmsHome ?? [];
?>
<section class="welcome-section container">
    <div class="welcome-text">
        <h2><?= htmlspecialchars($h['welcome_heading'] ?? '') ?></h2>
        <div class="cms-html"><?= \App\Core\HtmlSanitizer::purify($h['welcome_p1'] ?? '') ?></div>
        <div class="cms-html"><?= \App\Core\HtmlSanitizer::purify($h['welcome_p2'] ?? '') ?></div>
    </div>
</section>
