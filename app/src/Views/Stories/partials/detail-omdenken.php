<?php
/**
 * Partial: detail-omdenken.php
 * Template: omdenken
 */

$highlights = json_decode((string)($detailPage['highlights'] ?? '[]'), true);
$gallery    = json_decode((string)($detailPage['gallery']    ?? '[]'), true);
if (!is_array($highlights)) $highlights = [];
if (!is_array($gallery))    $gallery    = [];

$ageText   = $vm->getStoryAgeText();
$dayText   = $vm->getStoryDayText();
$timeText  = $vm->getStoryTimeText();
$typeText  = $vm->getStoryTypeText();

$assets = $vm->getOmdenkenAssets();

$heroImage   = ($detailPage['hero_image']         ?? '') ?: $assets['hero'];
$heroHeading = ($detailPage['hero_heading']        ?? '') ?: 'OMDENKEN – LIVE PODCAST SESSION';
$heroSubtitle= ($detailPage['article_title']       ?? '') ?: 'Changing perspectives through real stories.';
$heroDesc    = ($detailPage['hero_description']    ?? '') ?: 'This live podcast session invites visitors to explore how everyday challenges can be transformed into meaningful insights. Through honest conversations and personal experiences, the speakers share stories that inspire reflection, resilience, and new ways of thinking.';
$posterImage = ($detailPage['article_image']       ?? '') ?: $assets['poster'];
$block1Title = ($highlights[0]['title']            ?? '') ?: "What you'll experience";
$block1Text  = ($highlights[0]['description']      ?? '') ?: 'Real-life stories from inspiring speakers. Fresh perspectives on personal and social challenges. Thought-provoking conversations with practical takeaways. A relaxed, intimate atmosphere for listening and reflection.';
$block2Text  = ($detailPage['article_paragraph_1'] ?? '') ?: "Omdenken gives you practical tools to reframe frustration, find new angles, and turn stuck situations into surprising opportunities. You'll walk away with ideas you can apply immediately, at work, at home, or in your personal life.";
$block3Title = ($highlights[1]['title']            ?? '') ?: 'Why this story matters';
$block3Text  = ($detailPage['article_paragraph_2'] ?? '') ?: 'This session goes beyond entertainment. It offers space to slow down, listen, and connect with real experiences from people in Haarlem and beyond. Each story encourages empathy, curiosity, and a deeper understanding of everyday life.';
$blockImg1   = ($gallery[0]['image']               ?? '') ?: $assets['block1'];
$blockImg2   = ($gallery[1]['image']               ?? '') ?: $assets['block2'];
$noteText    = ($detailPage['article_paragraph_3'] ?? '') ?: 'This is a live podcast recording with a limited number of seats. Reservation is required to guarantee entry.';
?>

<section class="omdenken-page">

    <section class="omdenken-hero">
        <div class="omdenken-hero-image">
            <img src="<?= h($heroImage) ?>" alt="<?= h($heroHeading) ?>">
            <div class="omdenken-hero-overlay"></div>
            <div class="omdenken-hero-copy">
                <h1 class="omdenken-title"><?= h($heroHeading) ?></h1>
                <p class="omdenken-subtitle"><?= h($heroSubtitle) ?></p>
                <p class="omdenken-hero-text"><?= nl2br(h($heroDesc)) ?></p>
            </div>
        </div>
    </section>

    <section class="omdenken-content">

        <div class="omdenken-top-grid">
            <div class="omdenken-poster-card">
                <img src="<?= h($posterImage) ?>" alt="<?= h($story['name'] ?? 'Story') ?>">
            </div>
            <div class="omdenken-event-meta">
                <?php if ($ageText): ?>
                    <div class="omdenken-meta-line"><span>Age Group :</span> <?= h($ageText) ?></div>
                <?php endif; ?>
                <?php if ($dayText): ?>
                    <div class="omdenken-meta-line"><span>Day :</span> <?= h($dayText) ?></div>
                <?php endif; ?>
                <?php if ($timeText): ?>
                    <div class="omdenken-meta-line"><span>Time :</span> <?= h($timeText) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="omdenken-story-grid">
            <div class="omdenken-text-block">
                <h3><?= h($block1Title) ?></h3>
                <p><?= nl2br(h($block1Text)) ?></p>
            </div>
            <div class="omdenken-text-block omdenken-text-block-right">
                <p class="omdenken-right-copy"><?= nl2br(h($block2Text)) ?></p>
            </div>
        </div>

        <div class="omdenken-story-grid omdenken-story-grid-second">
            <div class="omdenken-text-block">
                <h3><?= h($block3Title) ?></h3>
                <p><?= nl2br(h($block3Text)) ?></p>
            </div>
            <div class="omdenken-host-image-wrap">
                <img src="<?= h($blockImg1) ?>" alt="Omdenken block image">
            </div>
        </div>

        <div class="omdenken-player-section">
            <div class="omdenken-player-poster">
                <img src="<?= h($blockImg2) ?>" alt="Omdenken visual">
                <div class="omdenken-poster-caption">
                    <strong><?= h($story['name'] ?? 'Story') ?></strong>
                    <span><?= h($typeText ?: 'Podcast') ?></span>
                </div>
            </div>
            <div class="omdenken-player-ui">
                <div class="player-top-row">
                    <button type="button" class="player-mini-btn">×</button>
                    <button type="button" class="player-mini-btn">◀</button>
                    <button type="button" class="player-play-btn">▶</button>
                    <button type="button" class="player-mini-btn">▶</button>
                    <button type="button" class="player-mini-btn">↻</button>
                </div>
                <div class="player-time-row">
                    <span>0:00</span>
                    <span>4:03</span>
                </div>
                <div class="player-progress-wrap">
                    <div class="player-progress-bar">
                        <div class="player-progress-fill"></div>
                    </div>
                </div>
                <div class="player-volume-row">
                    <span>♡</span>
                    <div class="player-volume-bar">
                        <div class="player-volume-fill"></div>
                    </div>
                    <span>🔊</span>
                </div>
                <div class="player-recent-title">Recent Podcast</div>
                <div class="player-tracklist">
                    <div class="track-row active">
                        <div class="track-info">
                            <strong><?= h($story['name'] ?? 'Story') ?></strong>
                            <small><?= h($typeText ?: 'Podcast') ?></small>
                        </div>
                        <span><?= h($timeText ?: '–') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="omdenken-stats-row">
            <div class="omdenken-stat-card">
                <div class="omdenken-stat-icon">🕒</div>
                <div class="omdenken-stat-title">TIME</div>
                <div class="omdenken-stat-value"><?= h($timeText ?: 'TBA') ?></div>
            </div>
            <div class="omdenken-stat-card">
                <div class="omdenken-stat-icon">👥</div>
                <div class="omdenken-stat-title">AGE</div>
                <div class="omdenken-stat-value"><?= h($ageText ?: '16+') ?></div>
            </div>
            <div class="omdenken-stat-card">
                <div class="omdenken-stat-icon">🌐</div>
                <div class="omdenken-stat-title">LANG</div>
                <div class="omdenken-stat-value"><?= h($vm->getStoryLanguageText() ?: 'NL') ?></div>
            </div>
        </div>

        <div class="omdenken-note-row">
            <div class="omdenken-note-label">Note:</div>
            <div class="omdenken-note-text"><?= nl2br(h($noteText)) ?></div>
        </div>

        <div class="omdenken-ticket-row">
            <a class="omdenken-ticket-btn" href="<?= h($vm->getTicketUrl()) ?>">BUY TICKETS</a>
        </div>

    </section>
</section>
