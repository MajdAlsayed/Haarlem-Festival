<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($viewModel->hero['title'] ?? '') ?></title>

    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/history.css">
</head>
<body class="history-page">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- HERO -->
    <?php
    $sectionModifier = '';
    $titleModifier = 'history-hero-title--yellow';
    $showSubtitle = true;
    $showButton = false;
    require __DIR__ . '/../partials/history/history-hero.php';
    ?>

    <!-- BREADCRUMBS -->
    <?php
    $breadcrumbs = [
        ['label' => 'HOME', 'url' => '/'],
        ['label' => 'HISTORY', 'url' => '/history'],
        ['label' => 'TOURS', 'url' => null],
    ];
    require __DIR__ . '/../partials/history/history-breadcrumb.php';
    ?>

    <!-- IMPORTANT INFORMATION -->
    <section class="history-tours-info">
        <div class="container">
            <h2>
                <?= htmlspecialchars($viewModel->infoCards['title'] ?? '') ?>
            </h2>
            <div class="history-tours-info-cards">
                <?php foreach ($viewModel->infoCards['cards'] ?? [] as $card): ?>
                    <div class="history-tours-info-card">
                        <h3>
                            <?= htmlspecialchars($card['title']) ?>
                        </h3>
                        <?php if (!empty($card['items'])): ?>
                            <ul>
                                <?php foreach ($card['items'] as $item): ?>
                                    <li>
                                        <?= htmlspecialchars($item) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>
                                <?= htmlspecialchars($card['text']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SCHEDULE + TOUR DETAILS -->
    <section class="history-tours-schedule-section">
        <div class="container">
            <div class="history-tours-schedule-wrapper">

                <!-- LEFT: Schedule -->
                <div class="history-tours-schedule">
                    <h2>
                        SCHEDULE YOUR TOUR
                    </h2>

                    <!-- Day tabs -->
                    <div class="history-tours-day-tabs">
                        <?php $first = true; ?>
                        <?php foreach ($viewModel->toursByDay as $date => $tours): ?>
                            <button class="history-tours-day-tab <?= $first ? 'active' : '' ?>"
                                    data-filter="<?= htmlspecialchars($date) ?>">
                                <?= htmlspecialchars(date('l', strtotime($date))) ?>
                            </button>
                            <?php $first = false; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tours per day - all panels rendered, JS shows/hides -->
                    <?php $first = true; ?>
                    <?php foreach ($viewModel->toursByDay as $date => $tours): ?>
                        <div class="history-tours-day-panel <?= $first ? 'active' : '' ?>"
                             data-filter="<?= htmlspecialchars($date) ?>">
                            <p class="history-tours-day-title">
                                Available Times for <?= htmlspecialchars(date('l', strtotime($date))) ?>
                            </p>
                            <?php foreach ($tours as $tour): ?>
                                <div class="history-tours-item">
                                    <div class="history-tours-time-lang">
                                        <span class="history-tours-time">
                                            <?= htmlspecialchars(date('H:i', strtotime($tour['start_time']))) ?>
                                        </span>
                                        <span class="history-tours-lang">
                                            <?= htmlspecialchars($tour['flag']) ?>
                                            <?= htmlspecialchars($tour['language_name']) ?>
                                        </span>
                                    </div>
                                    <span class="history-tours-spots">
                                        <?= htmlspecialchars($tour['tickets_available']) ?> spots left
                                    </span>
                                    <a href="/tickets" class="history-button-big history-button-big--yellow">
                                        ADD TO CART
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                </div>

                <!-- RIGHT: Tour Details -->
                <div class="history-tours-details">
                    <h2>
                        <?= htmlspecialchars($viewModel->tourDetails['title'] ?? '') ?>
                    </h2>
                    <div class="history-tours-details-list">
                        <?php foreach ($viewModel->tourDetails['items'] ?? [] as $item): ?>
                            <?php
                            $icon = match ($item['label']) {
                                'Duration' => '/images/history/icons/time-icon.svg',
                                'Group Size' => '/images/history/icons/group-icon.svg',
                                'Meeting Point' => '/images/history/icons/location-icon.svg',
                                'Languages' => '/images/history/icons/language-icon.svg',
                                'Includes' => '/images/history/icons/cup-icon.svg',
                                default => null
                            };
                            ?>
                            <div class="history-tours-detail-item">
                                <div class="history-tours-detail-icon">
                                    <?php if ($icon): ?>
                                        <img src="<?= htmlspecialchars($icon) ?>"
                                             alt="<?= htmlspecialchars($item['label']) ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="history-tours-detail-content">
                                    <p>
                                        <?= htmlspecialchars($item['label']) ?>
                                    </p>
                                    <p>
                                        <?= htmlspecialchars($item['value']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- WHAT'S INCLUDED -->
    <section class="history-tours-tickets">
        <div class="container">
            <h2>
                <?= htmlspecialchars($viewModel->ticketOptions['title'] ?? '') ?>
            </h2>
            <div class="history-tours-tickets-cards">
                <?php foreach ($viewModel->ticketOptions['tickets'] ?? [] as $ticket): ?>
                    <div class="history-tours-ticket-card">
                        <h3>
                            <?= htmlspecialchars($ticket['name']) ?>
                        </h3>
                        <p class="history-tours-ticket-per">
                            <?= htmlspecialchars($ticket['per']) ?>
                        </p>
                        <ul>
                            <?php foreach ($ticket['items'] as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ROUTE MAP -->
    <?php
    $mapLocations = $viewModel->locations;
    require __DIR__ . '/../partials/history/history-map.php';
    ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
    (function () {
        var tabs = document.querySelectorAll('.history-tours-day-tab');
        var panels = document.querySelectorAll('.history-tours-day-panel');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var filter = this.getAttribute('data-filter');

                // Update tabs
                tabs.forEach(function (t) {
                    t.classList.remove('active');
                });
                this.classList.add('active');

                // Update panels
                panels.forEach(function (p) {
                    if (p.getAttribute('data-filter') === filter) {
                        p.classList.add('active');
                    } else {
                        p.classList.remove('active');
                    }
                });
            });
        });
    })();
</script>
</body>
</html>