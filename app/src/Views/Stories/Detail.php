<?php
/** @var \App\ViewModels\StoriesViewModel $vm */
$app = $vm->appSettings;

if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

$story      = $vm->story;
$detailPage = is_array($vm->detailPage ?? null) ? $vm->detailPage : [];
$template   = $vm->getTemplate(); // 'omdenken' | 'buurderij' | 'generic'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($vm->pageTitle ?? 'Story Detail') ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= h($app['css_version'] ?? '1') ?>">
    <link rel="stylesheet" href="/css/Stories/details.css?v=<?= h($app['css_version'] ?? '1') ?>">
</head>
<body class="stories-detail-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="detail-page">

    <?php if ($template === 'generic'): ?>
    <nav class="stories-breadcrumb" aria-label="Breadcrumb">
        <div class="stories-breadcrumb-inner">
            <a href="/" class="stories-breadcrumb-link">HOME</a>
            <span class="stories-breadcrumb-separator">→</span>
            <a href="/stories" class="stories-breadcrumb-link">STORIES</a>



            <span class="stories-breadcrumb-separator">→</span>
            <span class="stories-breadcrumb-link active" aria-current="page">
                <?= h($story['name'] ?? 'Story') ?>
            </span>
        </div>
    </nav>
    <?php endif; ?>

    <?php if (!$story): ?>

        <h1 class="detail-not-found" style="padding:60px 34px;">Story not found.</h1>

    <?php else: ?>

        <?php require __DIR__ . "/partials/detail-{$template}.php"; ?>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
