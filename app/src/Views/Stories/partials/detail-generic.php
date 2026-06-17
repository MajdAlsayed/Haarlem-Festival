<?php
/**
 * Partial: detail-generic.php
 * Template: generic
 *
 * Professional layout using stories table data out of the box.
 * CMS detail page enhances it when admin adds content.
 * No payment/contribution widget.
 */

$highlights = $vm->getDetailHighlights();
$gallery = $vm->getDetailGallery();

// Story table data — always available
$storyName  = $story['name']        ?? '';
$storyDesc  = $story['description'] ?? '';
$storyImage = $story['image_path']  ?? '/images/Stories/cards/default.jpg';

$ageText   = $vm->getStoryAgeText();
$dayText   = $vm->getStoryDayText();
$timeText  = $vm->getStoryTimeText();
$endTimeText = $vm->getStoryEndTimeText();
$typeText  = $vm->getStoryTypeText();
$langText  = $vm->getStoryLanguageText();
$venueText = $vm->getStoryVenueText();
$ticketDetailsId = $vm->getTicketDetailsId();

// CMS overrides — fall back to story data only, no hardcoded strings
$heroImage   = ($detailPage['hero_image']         ?? '') ?: $storyImage;
$heroTitle   = ($detailPage['hero_heading']        ?? '') ?: $storyName;
$introText   = ($detailPage['hero_description']    ?? '') ?: $storyDesc;
$articleTitle= ($detailPage['article_title']       ?? '');
$mainImage   = ($detailPage['article_image']       ?? '');
$mainText1   = ($detailPage['article_paragraph_1'] ?? '');
$mainText2   = ($detailPage['article_paragraph_2'] ?? '');
$mainText3   = ($detailPage['article_paragraph_3'] ?? '');

$galleryItems = array_values(array_filter(
    $gallery,
    function ($item) {
        return !empty($item['image']);
    }
));

$tags = array_filter([
    $typeText,
    $langText,
    $ageText ? 'Age ' . $ageText : '',
]);
?>

<section class="generic-detail">

    <!-- ── Hero ── -->
    <div class="generic-hero" style="background-image:url('<?= h($heroImage) ?>');">
        <div class="generic-hero-overlay"></div>
        <div class="generic-hero-inner">
            <?php if (!empty($tags)): ?>
                <div class="generic-hero-tags">
                    <?php foreach ($tags as $tag): ?>
                        <span class="generic-tag"><?= h($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <h1 class="generic-hero-title"><?= h($heroTitle) ?></h1>
        </div>
    </div>

    <!-- ── Two-column body ── -->
    <div class="generic-body">

        <!-- Left: content -->
        <div class="generic-col-main">

            <!-- Description / intro -->
            <?php if ($introText): ?>
                <div class="generic-card">
                    <p class="generic-intro-text"><?= nl2br(h($introText)) ?></p>
                </div>
            <?php endif; ?>

            <!-- CMS article content — only shown when admin fills it in -->
            <?php if ($articleTitle || $mainImage || $mainText1): ?>
                <div class="generic-card">
                    <?php if ($articleTitle): ?>
                        <h2 class="generic-card-title"><?= h($articleTitle) ?></h2>
                    <?php endif; ?>
                    <?php if ($mainImage): ?>
                        <div class="generic-article-img">
                            <img src="<?= h($mainImage) ?>" alt="<?= h($articleTitle ?: $heroTitle) ?>">
                        </div>
                    <?php endif; ?>
                    <?php if ($mainText1): ?>
                        <p><?= nl2br(h($mainText1)) ?></p>
                    <?php endif; ?>
                    <?php if ($mainText2): ?>
                        <p><?= nl2br(h($mainText2)) ?></p>
                    <?php endif; ?>
                    <?php if ($mainText3): ?>
                        <p><?= nl2br(h($mainText3)) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Highlights from CMS -->
            <?php
            $validHighlights = array_filter(
                $highlights,
                function ($h) {
                    return !empty($h['title']) || !empty($h['description']);
                }
            );
            ?>
            <?php if (!empty($validHighlights)): ?>
                <div class="generic-card">
                    <h2 class="generic-card-title">Highlights</h2>
                    <div class="generic-highlights">
                        <?php foreach ($validHighlights as $hl): ?>
                            <div class="generic-highlight-row">
                                <div class="generic-highlight-dot"></div>
                                <div>
                                    <?php if (!empty($hl['title'])): ?>
                                        <strong><?= h($hl['title']) ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($hl['description'])): ?>
                                        <p><?= nl2br(h($hl['description'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Gallery from CMS -->
            <?php if (!empty($galleryItems)): ?>
                <div class="generic-card">
                    <h2 class="generic-card-title">Gallery</h2>
                    <div class="generic-gallery-grid">
                        <?php foreach ($galleryItems as $item): ?>
                            <div class="generic-gallery-item">
                                <img src="<?= h($item['image']) ?>"
                                     alt="<?= h($item['heading'] ?? '') ?>">
                                <?php if (!empty($item['heading'])): ?>
                                    <h4><?= h($item['heading']) ?></h4>
                                <?php endif; ?>
                                <?php if (!empty($item['text'])): ?>
                                    <p><?= nl2br(h($item['text'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Right: event info + ticket button -->
        <div class="generic-col-side">

            <div class="generic-info-card">
                <h3 class="generic-info-heading">Event Info</h3>

                <dl class="generic-info-list">
                    <?php if ($dayText): ?>
                        <div class="generic-info-row">
                            <dt>📅 Day</dt>
                            <dd><?= h($dayText) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($timeText): ?>
                        <div class="generic-info-row">
                            <dt>🕒 Time</dt>
                            <dd><?= h($timeText) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($endTimeText): ?>
                        <div class="generic-info-row">
                            <dt>End Time</dt>
                            <dd><?= h($endTimeText) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($langText): ?>
                        <div class="generic-info-row">
                            <dt>🌐 Language</dt>
                            <dd><?= h($langText) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($venueText): ?>
                        <div class="generic-info-row">
                            <dt>Venue</dt>
                            <dd><?= h($venueText) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($ageText): ?>
                        <div class="generic-info-row">
                            <dt>👥 Age</dt>
                            <dd><?= h($ageText) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($typeText): ?>
                        <div class="generic-info-row">
                            <dt>🎭 Type</dt>
                            <dd><?= h($typeText) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>

                <div class="generic-ticket-wrap">
                    <p class="generic-ticket-note">
                        Reservation is required to guarantee entry. Seats are limited.
                    </p>
                    <?php if ($ticketDetailsId > 0): ?>
                        <button type="button" class="generic-ticket-btn add-to-cart-button" data-ticket-details-id="<?= $ticketDetailsId ?>">
                            Buy Tickets &rsaquo;
                        </button>
                    <?php else: ?>
                        <a href="<?= h($vm->getTicketUrl()) ?>" class="generic-ticket-btn">
                            Buy Tickets &rsaquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</section>
