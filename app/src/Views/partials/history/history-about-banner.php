<section class="history-about-banner <?= $sectionModifier ?? '' ?>">
    <div class="container">
        <?php if ($showTitle ?? false): ?>
            <h2 class="history-about-banner-title"><?= htmlspecialchars($viewModel->aboutBanner['title'] ?? '') ?></h2>
        <?php endif; ?>
        <div class="history-about-banner-content">
            <p class="history-about-banner-text <?= $textModifier ?? '' ?>"><?= htmlspecialchars($viewModel->aboutBanner['text'] ?? '') ?></p>
        </div>
    </div>
</section>
