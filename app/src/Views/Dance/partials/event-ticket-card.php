<?php
/** One ticket card on the dance event page. Data is prepared in DanceEventService. */
/** @var array<string,mixed> $card */
/** @var \App\ViewModels\EventDetailViewModel $vm */
$h = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<article class="<?= $card['cardClass'] ?>">
    <?php if ($card['badge'] === 'VIP'): ?>
        <span class="event-detail-ticket-badge event-detail-ticket-badge--vip-figma">VIP</span>
    <?php elseif ($card['badge'] === 'BEST VALUE'): ?>
        <span class="event-detail-ticket-badge event-detail-ticket-badge--best">BEST VALUE</span>
    <?php endif; ?>

    <div class="event-detail-ticket-card-head">
        <h3 class="event-detail-ticket-title"><?= $h($card['title']) ?></h3>
        <p class="event-detail-ticket-price"><?= $h($card['priceLabel']) ?></p>
    </div>

    <?php if ($card['stockState'] === 'soldout'): ?>
        <p class="event-detail-stock-badge event-detail-stock-badge--soldout">Sold out</p>
    <?php elseif ($card['stockState'] === 'nearly'): ?>
        <p class="event-detail-stock-badge event-detail-stock-badge--nearly">Almost sold out</p>
    <?php elseif ($card['stockState'] === 'low'): ?>
        <p class="event-detail-stock-badge event-detail-stock-badge--low">Only <?= $h($card['remaining']) ?> left</p>
    <?php endif; ?>

    <?php if ($card['featureColumns'] !== []): ?>
        <?php if ($card['featuresWrapped']): ?><div class="event-detail-ticket-features-cols"><?php endif; ?>
        <?php foreach ($card['featureColumns'] as $column): ?>
            <ul class="event-detail-ticket-features-list event-detail-ticket-features-list--figma">
                <?php foreach ($column as $line): ?>
                    <li><?= $h($line) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
        <?php if ($card['featuresWrapped']): ?></div><?php endif; ?>
    <?php elseif ($card['fallbackFeature'] !== ''): ?>
        <p class="event-detail-ticket-features event-detail-ticket-features--figma"><?= $h($card['fallbackFeature']) ?></p>
    <?php endif; ?>

    <?php if ($card['meta'] !== ''): ?>
        <p class="event-detail-pass-meta event-detail-pass-meta--figma"><?= $h($card['meta']) ?></p>
    <?php endif; ?>

    <?php if ($card['canBuy']): ?>
        <!-- add to cart with quantity 1; the cart page handles edits -->
        <form method="post" action="/cart/add" class="event-detail-cart-form">
            <input type="hidden" name="_csrf" value="<?= $h($vm->cartFormCsrf) ?>">
            <input type="hidden" name="ticket_details_id" value="<?= (int) $card['tdId'] ?>">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="return" value="<?= $h($vm->cartReturn) ?>">
            <button type="submit" class="<?= $card['buttonClass'] ?>">Buy tickets</button>
        </form>
    <?php elseif ($card['tdId'] > 0 && $card['stockState'] === 'soldout'): ?>
        <p class="event-detail-cart-form"><span class="btn btn--light btn--block is-disabled" aria-disabled="true">Sold out</span></p>
    <?php endif; ?>
</article>
