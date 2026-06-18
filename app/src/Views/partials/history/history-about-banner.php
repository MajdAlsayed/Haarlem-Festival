<section class="history-about-banner <?= htmlspecialchars($sectionModifier ?? '') ?>">
    <div class="container">
        <?php if ($showTitle ?? false): ?>
            <h2 class="section-title section-title--underlined history-about-banner-title">
                <?= htmlspecialchars($viewModel->aboutBanner['title'] ?? '') ?>
            </h2>
        <?php endif; ?>
        <div class="history-about-banner-content">
            <div class="copy-text history-about-banner-text <?= htmlspecialchars($textModifier ?? '') ?> cms-html">
                <?= $viewModel->aboutBanner['text'] ?? '' ?>
            </div>
        </div>
    </div>
</section>
