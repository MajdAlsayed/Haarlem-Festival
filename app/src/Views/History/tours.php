<?php

// Settings for the page title, styles, body class
$pageTitle = $viewModel->hero['title'] ?? 'History — Haarlem Festival';
$pageStyles = ['/css/pages/history.css'];
$bodyClass = 'history-page';

// Hero settings
$pageHeroTitle = $viewModel->hero['title'] ?? 'Tours';
$pageHeroSubtitle = strip_tags($viewModel->hero['subtitle'] ?? '');
$pageHeroImage = $viewModel->heroImage?->imageUrl ?? '';
$pageHeroAlt = $viewModel->heroImage?->altText ?? $pageHeroTitle;
$pageHeroClass = 'history-detail-hero history-tours-hero';
$pageHeroContentClass = 'history-detail-hero__content';

$breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'History', 'url' => '/history'],
        ['label' => 'Landmarks', 'url' => '/history/locations'],
        ['label' => 'Tours', 'url' => null],
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../partials/head.php'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<?php require __DIR__ . '/../partials/header.php'; ?>

<main>
    <!-- HERO -->
    <?php require __DIR__ . '/../partials/page-hero.php'; ?>

    <!-- BREADCRUMBS -->
    <?php require __DIR__ . '/../partials/breadcrumbs.php'; ?>

    <!-- IMPORTANT INFORMATION -->
    <section class="history-tours-info">
        <div class="container">
            <h2 class="section-title section-title--accent history-tours-section-title">
                <?= htmlspecialchars($viewModel->infoCards['title'] ?? '') ?>
            </h2>
            <div class="history-tours-info-cards">
                <?php foreach ($viewModel->infoCards['cards'] ?? [] as $card): ?>
                    <div class="history-tours-info-card">
                        <h3 class="section-subtitle history-tours-info-card-title">
                            <?= htmlspecialchars($card['title'] ?? '') ?>
                        </h3>
                        <?php if (!empty($card['items'])): ?>
                            <ul class="copy-text copy-text--sm history-tours-info-card-list">
                                <?php foreach ($card['items'] as $item): ?>
                                    <li>
                                        <?= htmlspecialchars($item) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="copy-text copy-text--sm history-tours-info-card-text">
                                <?= htmlspecialchars($card['text'] ?? '') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SELECT TICKET -->
    <section class="history-tours-tickets">
        <div class="container">
            <h2 class="section-title history-tours-section-title history-tours-section-title--center">
                <?= htmlspecialchars($viewModel->ticketOptions['title'] ?? '') ?>
            </h2>
            <div class="history-tours-tickets-cards">
                <?php $ticketIndex = 0; ?>
                <?php foreach ($viewModel->ticketOptions['tickets'] ?? [] as $ticket): ?>
                    <div class="festival-card festival-card--history history-tours-ticket-card <?= $ticketIndex === 0 ? 'history-tours-ticket-card--selected' : '' ?>"
                         data-ticket-type="<?= $ticketIndex === 0 ? 'regular' : 'family' ?>"
                         style="cursor: pointer;">
                        <h3 class="section-subtitle history-tours-ticket-card-title">
                            <?= htmlspecialchars($ticket['name']) ?>
                        </h3>
                        <?php if (!empty($ticket['price'])): ?>
                            <p class="section-title section-title--accent history-tours-ticket-price">
                                <?= htmlspecialchars($ticket['price']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="copy-text copy-text--sm history-tours-ticket-per">
                            <?= htmlspecialchars($ticket['per']) ?>
                        </p>
                        <ul class="copy-text copy-text--sm history-tours-ticket-list">
                            <?php foreach ($ticket['items'] as $item): ?>
                                <li>
                                    <?= htmlspecialchars($item) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php $ticketIndex++; ?>
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
                    <h2 class="section-title history-tours-section-title">
                        SCHEDULE YOUR TOUR
                    </h2>

                    <!-- Day tabs -->
                    <div class="filter-tabs history-tours-day-tabs">
                        <?php $first = true; ?>
                        <?php foreach ($viewModel->toursByDay as $date => $tours): ?>
                            <button class="filter-tab history-tours-day-tab <?= $first ? 'active' : '' ?>"
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
                            <p class="section-subtitle history-tours-day-title">
                                Available Times for <?= htmlspecialchars(date('l', strtotime($date))) ?>
                            </p>
                            <div class="history-tours-slots-grid">
                                <?php foreach ($tours as $tour): ?>
                                    <div class="festival-card festival-card--history history-tours-item">
                                        <div class="history-tours-time-lang">
                                        <span class="section-subtitle history-tours-time">
                                            <?= htmlspecialchars(date('H:i', strtotime($tour['start_time']))) ?>
                                        </span>
                                            <span class="copy-text copy-text--sm history-tours-lang">
                                            <?= htmlspecialchars($tour['flag']) ?>
                                            <?= htmlspecialchars($tour['language_name']) ?>
                                        </span>
                                        </div>
                                        <button
                                                type="button"
                                                class="btn btn--light btn--sm btn--block history-tours-add-button add-to-cart-button"
                                                data-ticket-details-id="<?= (int) $tour['ticket_details_id'] ?>"
                                                data-ticket-family-id="<?= (int) $tour['ticket_family_id'] ?>"
                                        >
                                            ADD TO CART
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $first = false; ?>
                    <?php endforeach; ?>
                </div>

                <!-- RIGHT: Tour Details -->
                <div class="history-tours-details">
                    <h2 class="section-title history-tours-section-title">
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
                                    <p class="section-subtitle history-tours-detail-label">
                                        <?= htmlspecialchars($item['label']) ?>
                                    </p>

                                    <p class="copy-text copy-text--sm history-tours-detail-value">
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



    <!-- ROUTE MAP: Leaflet map with all tour locations from the database -->
    <?php
    $mapLocations = $viewModel->locations;
    require __DIR__ . '/../partials/history/history-map.php';
    ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
    // Day tab filtering
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

    // Ticket type selection — remember which card (Regular or Family) the user clicked
    (function () {
        var selectedType = 'regular'; // default — first card is selected on load

        // Handle ticket card click — highlight selected card
        document.querySelectorAll('.history-tours-ticket-card').forEach(function (card) {
            card.addEventListener('click', function () {
                // Remove selected state from all cards
                document.querySelectorAll('.history-tours-ticket-card').forEach(function (c) {
                    c.classList.remove('history-tours-ticket-card--selected');
                });

                // Mark this card as selected
                this.classList.add('history-tours-ticket-card--selected');

                // Remember the selected type (regular or family)
                selectedType = this.getAttribute('data-ticket-type');
            });
        });

        // Intercept ADD TO CART click BEFORE cartDrawer.js reads the ID
        // We use capture phase (true) so our listener fires first
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.history-tours-add-button');
            if (!btn) return;

            var regularId = btn.getAttribute('data-ticket-details-id');
            var familyId  = btn.getAttribute('data-ticket-family-id');

            // Swap the ID to the correct one before cartDrawer.js reads it
            if (selectedType === 'family' && familyId && familyId !== '0') {
                btn.setAttribute('data-ticket-details-id', familyId);
            } else {
                btn.setAttribute('data-ticket-details-id', regularId);
            }
        }, true); // true = capture phase — fires before cartDrawer.js bubble listener
    })();
</script>
</body>
</html>