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

// Settings for the page title, styles, body class
$pageTitle = $vm->pageTitle ?? 'Story Detail - Haarlem Festival';
$pageStyles = ['/css/pages/stories.css'];
$bodyClass = 'stories-page stories-detail-page';

// Breadcrumbs
$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Stories', 'url' => '/stories'],
        ['label' => $story['name'] ?? 'Story', 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= h($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="detail-page">

    <?php if (!$story): ?>

        <h1 class="detail-not-found" style="padding:60px 34px;">Story not found.</h1>

    <?php else: ?>

        <?php require __DIR__ . "/partials/detail-{$template}.php"; ?>

    <?php endif; ?>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>