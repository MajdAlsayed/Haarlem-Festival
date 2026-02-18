<?php $app = $app ?? (new \App\Repositories\SettingsRepository())->getAll(); ?>
<section id="events" class="events-section">
    <div class="container">
        <div class="section-header">
            <h2>Upcoming Festival &amp; Events</h2>
            <p class="section-subtitle">
                Explore what's happening in Haarlem this season.
            </p>
        </div>

        <div class="cards">
            <?php foreach ($events as $event):
                $imageName = $event->cardImage ?? strtolower($event->eventTypeName ?? '') . '.jpg';
                $imagePath = '/images/' . $imageName;
                $infoUrl = $event->infoPath ?? '#';
            ?>
                <article class="card event-card">
                    <div class="event-image-wrapper">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($event->title) ?>" class="event-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="event-image-placeholder" style="display:none;">
                            <div class="placeholder-icon">📷</div>
                        </div>
                        <div class="event-image-overlay">
                            <div class="event-content-overlay">
                                <h3 class="event-title"><?= htmlspecialchars($event->title) ?></h3>
                                <p class="event-location"><?= htmlspecialchars($app['default_event_location']) ?></p>
                                <p class="event-description"><?= htmlspecialchars($event->description) ?></p>
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
