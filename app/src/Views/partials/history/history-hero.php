<!-- Optional modifier class -->
<section class="page-hero history-hero-section <?= $sectionModifier ?? '' ?>">
    <div class="page-hero__background history-hero-background">
        <img
                src="<?= htmlspecialchars($viewModel->heroImage?->imageUrl ?? '') ?>"
                alt="<?= htmlspecialchars($viewModel->heroImage?->altText ?? '') ?>"
        >
    </div>
    <div class="container page-hero__inner">
        <div class="page-hero__content history-hero-content">
            <div class="history-hero-title-container">
            <h1 class="page-hero__title section-title--shadow htmlspecialchars($titleModifier ?? '') ?>">
                <?= nl2br(htmlspecialchars($viewModel->hero['title'] ?? '')) ?>
            </h1>
            <?php if ($showSubtitle ?? false): ?>
                <p class="page-hero__subtitle history-hero-subtitle cms-html">
                    <?= $viewModel->hero['subtitle'] ?? '' ?>
                </p>
            <?php endif; ?>
            <?php if ($showButton ?? false): ?>
                <div class="history-hero-bottom">
                    <p class="history-hero-description cms-html">
                        <?= $viewModel->hero['description'] ?? '' ?>
                    </p>
                    <a href="<?= htmlspecialchars($viewModel->hero['button_url'] ?? '') ?>"
                       class="btn btn--light">
                        <?= htmlspecialchars($viewModel->hero['button_text'] ?? '') ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</section>


