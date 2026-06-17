<?php
/**
 * Partial: detail-buurderij.php
 * Template: buurderij
 */

$highlights = json_decode((string)($detailPage['highlights'] ?? '[]'), true);
$gallery    = json_decode((string)($detailPage['gallery']    ?? '[]'), true);
if (!is_array($highlights)) $highlights = [];
if (!is_array($gallery))    $gallery    = [];

$ageText   = $vm->getStoryAgeText();
$dayText   = $vm->getStoryDayText();
$timeText  = $vm->getStoryTimeText();
$endTimeText = $vm->getStoryEndTimeText();
$venueText = $vm->getStoryVenueText();
$ticketDetailsId = $vm->getTicketDetailsId();

$assets = $vm->getBuurderijAssets();

$heroImage   = ($detailPage['hero_image']         ?? '') ?: $assets['hero'];
$heroTitle   = ($detailPage['hero_heading']        ?? '') ?: 'The Story of Buurderij Haarlem';
$introTitle  = ($detailPage['article_title']       ?? '') ?: 'What is "The Story of Buurderij Haarlem"?';
$introText   = ($detailPage['hero_description']    ?? '') ?: 'This story highlights how a local food initiative can become a meaningful social experience for the whole city. Through Buurderij Haarlem, visitors discover the connection between producers, residents, and everyday choices around food, sustainability, and community.';
$mainImage   = ($detailPage['article_image']       ?? '') ?: $assets['main'];
$mainTitle   = ($highlights[0]['title']            ?? '') ?: 'The Story Behind Buurderij Haarlem';
$mainText1   = ($detailPage['article_paragraph_1'] ?? '') ?: 'Buurderij Haarlem brings together local farmers, food makers, and residents in one welcoming place. The project makes food feel more personal and more transparent, because visitors meet the people behind the products directly.';
$mainText2   = ($detailPage['article_paragraph_2'] ?? '') ?: 'This story shows how local systems can strengthen a city socially as well as economically. In Haarlem, Buurderij reflects a wider movement toward sustainability, human connection, and community-based living.';
$bottomTitle = ($highlights[1]['title']            ?? '') ?: 'Why This Story Matters';
$bottomText  = ($detailPage['article_paragraph_3'] ?? '') ?: 'The Story of Buurderij Haarlem turns a local initiative into a bigger reflection on belonging, responsibility, and the way cities can be shaped by everyday choices.';
$bottomImage = ($gallery[0]['image']               ?? '') ?: $assets['main2'];

$gallery1 = [
    'image'   => ($gallery[0]['image']   ?? '') ?: $assets['gallery1'],
    'heading' => ($gallery[0]['heading'] ?? '') ?: 'Local food choices',
    'text'    => ($gallery[0]['text']    ?? '') ?: 'Fresh products, short supply chains, and meaningful contact between producers and residents.',
];
$gallery2 = [
    'image'   => ($gallery[1]['image']   ?? '') ?: $assets['gallery2'],
    'heading' => ($gallery[1]['heading'] ?? '') ?: 'Community gathering',
    'text'    => ($gallery[1]['text']    ?? '') ?: 'A welcoming space where people meet, exchange ideas, and rediscover the social side of food.',
];
$gallery3 = [
    'image'   => ($gallery[2]['image']   ?? '') ?: $assets['gallery3'],
    'heading' => ($gallery[2]['heading'] ?? '') ?: 'Sustainable local culture',
    'text'    => ($gallery[2]['text']    ?? '') ?: "Buurderij reflects Haarlem's creative, sustainable, and community-focused identity in everyday life.",
];
?>

<section class="buurderij-page">

    <section class="buurderij-hero">
        <div class="buurderij-hero-image">
            <img src="<?= h($heroImage) ?>" alt="<?= h($heroTitle) ?>">
            <div class="buurderij-hero-overlay"></div>
            <div class="buurderij-hero-copy">
                <h1 class="buurderij-title"><?= h($heroTitle) ?></h1>
                <p class="buurderij-hero-subtitle">Haarlem</p>
                <?php if ($ticketDetailsId > 0): ?>
                    <button type="button" class="buurderij-hero-btn add-to-cart-button" data-ticket-details-id="<?= $ticketDetailsId ?>" data-contribution-total="0">Book Now &rsaquo;</button>
                <?php else: ?>
                    <a class="buurderij-hero-btn" href="<?= h($vm->getTicketUrl()) ?>">Book Now &rsaquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <nav class="stories-breadcrumb" aria-label="Breadcrumb">
        <div class="stories-breadcrumb-inner">
            <a href="/" class="stories-breadcrumb-link">HOME</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">&rarr;</span>
            <a href="/stories" class="stories-breadcrumb-link">STORIES</a>
            <span class="stories-breadcrumb-separator" aria-hidden="true">&rarr;</span>
            <span class="stories-breadcrumb-link active" aria-current="page"><?= h($story['name'] ?? 'Buurderij Haarlem') ?></span>
        </div>
    </nav>

    <section class="buurderij-content">

        <section class="buurderij-intro">
            <div class="buurderij-intro-inner">
                <h2><?= h($introTitle) ?></h2>
                <p><?= nl2br(h($introText)) ?></p>
                <span>SCROLL DOWN</span>
                <div class="buurderij-scroll-pill"></div>
            </div>
        </section>

        <section class="buurderij-main-grid">
            <div class="buurderij-main-image">
                <img src="<?= h($mainImage) ?>" alt="Buurderij Haarlem">
            </div>
            <div class="buurderij-main-text">
                <h3><?= h($mainTitle) ?></h3>
                <p><?= nl2br(h($mainText1)) ?></p>
                <p><?= nl2br(h($mainText2)) ?></p>
            </div>
        </section>

        <section class="buurderij-gallery-grid">
            <?php foreach ([$gallery1, $gallery2, $gallery3] as $item): ?>
                <article class="buurderij-gallery-card">
                    <img src="<?= h($item['image']) ?>" alt="<?= h($item['heading']) ?>">
                    <h4><?= h($item['heading']) ?></h4>
                    <p><?= nl2br(h($item['text'])) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="buurderij-bottom-grid">

            <div class="buurderij-contribute-card">
                <h3>Contribution (pay as you like)</h3>

                <div class="buurderij-age-box">
                    <div class="buurderij-age-top">
                        <span class="buurderij-age-icon">!</span>
                        <span class="buurderij-age-text">Age requirement</span>
                        <span class="buurderij-age-badge"><?= h($ageText ?: '16+') ?></span>
                    </div>
                    <p>This session contains spoken-word content and is recommended for ages <?= h($ageText ?: '16+') ?>.</p>
                </div>

                <div class="buurderij-contribute-copy">
                    Choose the amount you want to contribute.<br>
                    Your contribution supports the storytellers and future events.<br>
                    EUR 0 is also welcome - just reserve
                </div>

                <div class="buurderij-amount-label">Suggested amounts</div>
                <div class="buurderij-amount-grid">
                    <button type="button" class="buurderij-amount-btn active" data-contribution-amount="0">EUR 0</button>
                    <button type="button" class="buurderij-amount-btn" data-contribution-amount="5">EUR 5</button>
                    <button type="button" class="buurderij-amount-btn" data-contribution-amount="10">EUR 10</button>
                    <button type="button" class="buurderij-amount-btn" data-contribution-amount="15">EUR 15</button>
                    <button type="button" class="buurderij-amount-btn" data-contribution-amount="20">EUR 20</button>
                </div>

                <div class="buurderij-custom-label">Or enter a custom amount</div>
                <div class="buurderij-custom-input-wrap">
                    <input type="text" class="buurderij-custom-amount" value="0" placeholder="0">
                </div>

                <div class="buurderij-total-row">
                    <span>Your contribution</span>
                    <strong class="buurderij-contribution-total">EUR 0.00</strong>
                </div>

                <?php if ($ticketDetailsId > 0): ?>
                    <button type="button" class="buurderij-reserve-btn add-to-cart-button" data-ticket-details-id="<?= $ticketDetailsId ?>" data-contribution-source="buurderij">Reserve &rsaquo;</button>
                <?php else: ?>
                    <a href="<?= h($vm->getTicketUrl()) ?>" class="buurderij-reserve-btn">Reserve &rsaquo;</a>
                <?php endif; ?>
            </div>

            <div class="buurderij-info-card buurderij-info-card--design">
                <div class="buurderij-info-top">
                    <div class="buurderij-info-block">
                        <strong>Day</strong>
                        <div class="buurderij-day-tabs buurderij-day-tabs-large">
                            <span class="active"><?= h($dayText ?: 'Thursday') ?></span>
                        </div>
                    </div>
                    <div class="buurderij-info-block">
                        <strong>Time</strong>
                        <span><?= h($timeText ?: '20:30') ?></span>
                    </div>
                    <div class="buurderij-info-block">
                        <strong>End time</strong>
                        <span><?= h($endTimeText ?: '21:45') ?></span>
                    </div>
                    <div class="buurderij-info-block">
                        <strong>Location</strong>
                        <span><?= h($venueText ?: 'Kweekcafe, Haarlem') ?></span>
                    </div>
                </div>

                <div class="buurderij-reservation-box buurderij-reservation-box-large">
                    <h4>Reservation</h4>
                    <ul>
                        <li>Reservation is required</li>
                        <li>Even for Pay As You Like events</li>
                        <li>Reservation guarantees entry (limited capacity)</li>
                        <li>Exclusive lounge access</li>
                        <li>Complimentary drinks</li>
                        <li>Meet &amp; greet opportunities</li>
                    </ul>
                </div>
            </div>

        </section>

        <section class="buurderij-last-grid">
            <div class="buurderij-last-image">
                <img src="<?= h($bottomImage) ?>" alt="Buurderij community">
            </div>
            <div class="buurderij-last-text">
                <h3><?= h($bottomTitle) ?></h3>
                <p><?= nl2br(h($bottomText)) ?></p>
            </div>
        </section>

    </section>
</section>
