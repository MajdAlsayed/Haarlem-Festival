<?php
declare(strict_types=1);
/**
 * Small include used on jazz artist pages so you don’t duplicate the same hidden fields everywhere.
 *
 * It POSTs to `/cart/add` with the catalog id, qty 1, CSRF, and a `return` URL so people land back on the artist page.
 * If there is no ticket row yet (`ticketDetailsId <= 0`), we show a plain link to the jazz tickets tab instead of a dead button.
 *
 * Variables: $ticketDetailsId, $returnUrl, $buttonClass, $buttonLabel, optional $fallbackLinkLabel.
 *
 * @var callable(string):string $h
 * @var int $ticketDetailsId
 * @var string $returnUrl
 * @var string $buttonClass space-separated classes for the submit (or link)
 * @var string $buttonLabel
 * @var string|null $fallbackLinkLabel anchor text when no ticket_details row (default: View jazz tickets)
 */

use App\Core\Csrf;

$tId = (int) ($ticketDetailsId ?? 0);
$returnUrl = trim((string) ($returnUrl ?? '/tickets?cat=jazz'));
if ($returnUrl === '' || !str_starts_with($returnUrl, '/')) {
    $returnUrl = '/tickets?cat=jazz';
}

$fallbackLinkLabel = isset($fallbackLinkLabel) && is_string($fallbackLinkLabel) && $fallbackLinkLabel !== ''
    ? $fallbackLinkLabel
    : 'View jazz tickets';

if ($tId <= 0) {
    echo '<a class="' . $h($buttonClass) . '" href="/tickets?cat=jazz">' . $h($fallbackLinkLabel) . '</a>';
    return;
}

$csrf = Csrf::peek('cart') ?? Csrf::token('cart');
?>
<form method="post" action="/cart/add" class="jazz-cart-add-form">
    <input type="hidden" name="ticket_details_id" value="<?= $tId ?>">
    <input type="hidden" name="quantity" value="1">
    <input type="hidden" name="_csrf" value="<?= $h($csrf) ?>">
    <input type="hidden" name="return" value="<?= $h($returnUrl) ?>">
    <button type="submit" class="<?= $h($buttonClass) ?>"><?= $h($buttonLabel) ?></button>
</form>
