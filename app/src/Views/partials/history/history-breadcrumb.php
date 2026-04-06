<nav class="breadcrumbs" aria-label="Breadcrumb">
    <div class="container">
        <?php foreach ($breadcrumbs as $i => $item): ?>
            <?php $isLast = $i === array_key_last($breadcrumbs); // Takes the last element in the array ?>
            <?php if ($isLast): ?>
                <span class="breadcrumb-current">
                    <?= htmlspecialchars($item['label']) ?>
                </span>
            <?php else: ?>
                <a href="<?= htmlspecialchars($item['url']) ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
                <span class="breadcrumb-sep">›</span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</nav>