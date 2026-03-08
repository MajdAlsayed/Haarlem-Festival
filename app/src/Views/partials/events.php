<?php
$app = $app ?? (new \App\Repositories\SettingsRepository())->getAll();
$categoryDisplayLabels = [
    'dance' => 'Music & Culture',
    'jazz' => 'Jazz',
    'history' => 'History & Culture',
    'yammy' => 'Food & Drinks',
    'stories' => 'Stories',
];
?>
<section id="events" class="events-section">
    <div class="container">
        <div class="section-header">
            <h2>Upcoming Festival &amp; Events</h2>
            <p class="section-subtitle">
                Explore what's happening in Haarlem this season.
            </p>
        </div>

        <div class="cards">
            <?php foreach ($categories as $category):
                $imageName = $category->cardImage ?? strtolower($category->name) . '.jpg';
                $imagePath = '/images/' . $imageName;
                $infoUrl = $category->infoPath ?? '#';
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
                                <div class="event-actions">
                                    <a href="<?= htmlspecialchars($infoUrl) ?>" class="event-link">INFO &gt;</a>
                                    <a href="#" class="event-link">TICKETS &gt;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
