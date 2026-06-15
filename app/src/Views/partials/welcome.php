<?php
/** @var array<string, string> $cmsHome */
$h = $cmsHome ?? [];
$welcomeHeading = trim((string) ($h['welcome_heading'] ?? ''));
$welcomeP1 = \App\Core\HtmlSanitizer::purify($h['welcome_p1'] ?? '');
$welcomeP2 = \App\Core\HtmlSanitizer::purify($h['welcome_p2'] ?? '');
$hasWelcome = $welcomeHeading !== ''
    || !\App\Core\HtmlSanitizer::isEmptyHtml($welcomeP1)
    || !\App\Core\HtmlSanitizer::isEmptyHtml($welcomeP2);
if (!$hasWelcome) {
    return;
}
?>
<section class="welcome-section">
    <div class="container">
        <div class="welcome-text">
            <h2 class="section-title section-title--accent section-title--underlined">
                <?= htmlspecialchars($welcomeHeading) ?>
            </h2>
            <div class="copy-text cms-html"><?= $welcomeP1 ?></div>
            <div class="copy-text cms-html"><?= $welcomeP2 ?></div>
        </div>
    </div>
</section>
