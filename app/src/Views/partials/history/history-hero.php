<!-- Optional modifier class -->
<section class="history-hero-section <?= $sectionModifier ?? '' ?>">
    <div class="history-hero-background">
        <img
                src="<?= htmlspecialchars($viewModel->heroImage?->imageUrl ?? '') ?>"
                alt="<?= htmlspecialchars($viewModel->heroImage?->altText ?? '') ?>"
        >
    </div>
    <div class="history-hero-content">
        <div class="container">
            <div class="history-hero-title-container">
                <h1 class="history-hero-title <?= htmlspecialchars($titleModifier ?? '') ?>">
                    <?= nl2br(htmlspecialchars($viewModel->hero['title'] ?? '')) ?>
                </h1>
                <?php if ($showSubtitle ?? false): ?>
                    <p class="history-hero-subtitle cms-html">
                        <?= $viewModel->hero['subtitle'] ?? '' ?>
                    </p>
                <?php endif; ?>
                <?php if ($showButton ?? false): ?>
                    <div class="history-hero-bottom">
                        <p class="history-hero-description cms-html">
                            <?=$viewModel->hero['description'] ?? '' ?>
                        </p>
                        <a href="<?= htmlspecialchars($viewModel->hero['button_url'] ?? '') ?>"
                           class="history-button-big">
                            <span class="history-button-text">
                                <?= htmlspecialchars($viewModel->hero['button_text'] ?? '') ?>
                            </span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>


