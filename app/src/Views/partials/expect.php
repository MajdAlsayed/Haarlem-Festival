<?php
$h = $cmsHome ?? [];
$cardsRaw = (string) ($h['expect_cards_json'] ?? '[]');
$cards = json_decode($cardsRaw, true);
if (!is_array($cards)) {
    $cards = [];
}
?>

<section class="expect-section">
    <div class="container">
        <h2 class="section-title section-title--underlined expect-heading">
            <?= htmlspecialchars((string) ($h['expect_heading'] ?? '')) ?>
        </h2>

        <p class="section-lead section-lead--center expect-intro">
            <?= htmlspecialchars((string) ($h['expect_intro'] ?? '')) ?>
        </p>

        <h3 class="expect-subheading">
            <?= htmlspecialchars((string) ($h['expect_subheading'] ?? '')) ?>
        </h3>

        <div class="expect-grid">
            <?php foreach ($cards as $card): ?>
                <?php
                $icon = is_array($card) ? (string) ($card['icon'] ?? '') : '';
                $title = is_array($card) ? (string) ($card['title'] ?? '') : '';
                $items = is_array($card) && is_array($card['items'] ?? null) ? $card['items'] : [];
                ?>

                <div class="festival-card festival-card--home expect-card">
                    <div class="expect-icon"><?= htmlspecialchars($icon) ?></div>

                    <h4 class="expect-card-title">
                        <?= htmlspecialchars($title) ?>
                    </h4>

                    <ul class="expect-list">
                        <?php foreach ($items as $item): ?>
                            <li><?= htmlspecialchars((string) $item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="expect-cta">
            <?= htmlspecialchars((string) ($h['expect_cta'] ?? '')) ?>
        </div>
    </div>
</section>