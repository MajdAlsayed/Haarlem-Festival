<?php
$breadcrumbs = $breadcrumbs ?? [];

if (empty($breadcrumbs)) {
    return;
}
?>

<div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <?php foreach ($breadcrumbs as $i => $item): ?>
            <?php $isLast = $i === array_key_last($breadcrumbs); ?>

            <?php if ($isLast): ?>
                <span class="breadcrumb-current">
                    <?= htmlspecialchars($item['label'] ?? '') ?>
                </span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($item['url'] ?? '#') ?>">
                    <?= htmlspecialchars($item['label'] ?? '') ?>
                </a>
                <span class="breadcrumb-separator">›</span>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</div>