<?php
use App\Repositories\TicketsRepository;

$app = $app ?? (new \App\Repositories\SettingsRepository())->getAll();
$h = $cmsHome ?? [];
$categoryDisplayLabels = [
    'dance' => (string) ($h['events_category_dance'] ?? ''),
    'jazz' => (string) ($h['events_category_jazz'] ?? ''),
    'history' => (string) ($h['events_category_history'] ?? ''),
    'yammy' => (string) ($h['events_category_yammy'] ?? ''),
    'stories' => (string) ($h['events_category_stories'] ?? ''),
];
?>
<section id="events" class="events-section">
    <div class="container">
        <div class="section-header">
            <h2><?= htmlspecialchars((string) ($h['events_heading'] ?? '')) ?></h2>
            <p class="section-subtitle">
                <?= htmlspecialchars((string) ($h['events_subtitle'] ?? '')) ?>
            </p>
        </div>

        <div class="cards">
            <?php foreach ($categories as $category):
                $imageName = $category->cardImage ?? strtolower($category->name) . '.jpg';
                $imagePath = '/images/' . $imageName;
                $rawInfo = (string) ($category->infoPath ?? '#');
                $infoUrl = $rawInfo === '' || $rawInfo === '#'
                    ? '#'
                    : ($rawInfo[0] === '/' ? $rawInfo : '/' . ltrim($rawInfo, '/'));
                $slug = strtolower(trim((string) ($category->name ?? '')));
                $showTickets = ($slug !== 'yammy');
                $ticketUrl = '/tickets';
                if ($showTickets) {
                    if ($slug === 'dance') {
                        $ticketUrl = '/dance';
                    } elseif (in_array($slug, TicketsRepository::CATEGORIES, true)) {
                        $ticketUrl = '/tickets?cat=' . rawurlencode($slug);
                    }
                }
                $cardTitle = $categoryDisplayLabels[strtolower($category->name)] ?? ucfirst($category->name);
            ?>
                <article class="card event-card">
                    <div class="event-image-wrapper">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($cardTitle) ?>" class="event-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="event-image-placeholder" style="display:none;">
                            <div class="placeholder-icon">📷</div>
                        </div>
                        <div class="event-image-overlay">
                            <div class="event-content-overlay">
                                <h3 class="event-title"><?= htmlspecialchars($cardTitle) ?></h3>
                                <p class="event-location"><?= htmlspecialchars($app['default_event_location']) ?></p>
                                <p class="event-description"><?= htmlspecialchars($category->description) ?></p>
                                <div class="event-actions<?= $showTickets ? '' : ' event-actions--single' ?>">
                                    <a href="<?= htmlspecialchars($infoUrl) ?>" class="event-link"><?= htmlspecialchars((string) ($h['events_info_label'] ?? '')) ?></a>
                                    <?php if ($showTickets): ?>
                                    <a href="<?= htmlspecialchars($ticketUrl) ?>" class="event-link"><?= htmlspecialchars((string) ($h['events_tickets_label'] ?? '')) ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
