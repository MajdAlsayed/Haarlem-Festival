<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\TicketRepository;

/** Capacity: sold seats plus active carts plus unpaid pay-later reservations before allowing add or checkout. */
final class TicketAvailabilityService
{
    public function __construct(
        private CartRepository $cartRepository,
        private TicketRepository $ticketRepository
    ) {
    }

    /**
     * Throws InvalidArgumentException if this delta would exceed capacity.
     * Delta: positive when adding seats, negative when removing from cart.
     */
    public function assertDeltaAllowed(int $ticketDetailsId, int $delta): void
    {
        if ($delta <= 0) {
            return;
        }

        $capacity = $this->cartRepository->getTicketDetailsCapacity($ticketDetailsId);
        if ($capacity === null) {
            return;
        }

        $sold = $this->ticketRepository->countSoldForTicketDetails($ticketDetailsId);
        $reserved = $this->cartRepository->sumActiveCartQuantityForTicketDetails($ticketDetailsId);
        $pending = $this->cartRepository->sumPendingOrderQuantityForTicketDetails($ticketDetailsId);
        $used = $sold + $reserved + $pending;

        // Requirement was “max 90% for single tickets”. We only apply it when someone adds one seat at a time
        // (delta 1). Buying 2+ in one click skips this extra rule and only hits the hard capacity check below.
        if ($delta === 1 && $capacity > 10) {
            $maxSingleShare = (int) max(0, floor(0.9 * $capacity));
            if ($used + $delta > $maxSingleShare) {
                throw new \InvalidArgumentException(
                    'Single-ticket sales are limited to 90% of capacity for this event. '
                    . 'Add more than one ticket in one go, or choose another option.'
                );
            }
        }

        if ($used + $delta > $capacity) {
            $remaining = max(0, $capacity - $used);
            throw new \InvalidArgumentException(
                $remaining > 0
                    ? "Only {$remaining} ticket(s) left for this option."
                    : 'This ticket option is sold out.'
            );
        }
    }

    /**
     * Re-check immediately before payment (call inside the same DB transaction as the order).
     * Counts sold + every active cart + pay-later pendings so two people cannot oversell the last seats.
     */
    public function assertCartCanCheckout(\App\ViewModels\CartViewModel $cart): void
    {
        foreach ($cart->items as $item) {
            $capacity = $this->cartRepository->getTicketDetailsCapacity($item->ticketDetailsId);
            if ($capacity === null) {
                continue;
            }
            $sold = $this->ticketRepository->countSoldForTicketDetails($item->ticketDetailsId);
            $reserved = $this->cartRepository->sumActiveCartQuantityForTicketDetails($item->ticketDetailsId);
            $pending = $this->cartRepository->sumPendingOrderQuantityForTicketDetails($item->ticketDetailsId);
            if ($sold + $reserved + $pending > $capacity) {
                throw new \RuntimeException(
                    'Availability changed while you were checking out. Please return to your cart and adjust quantities.'
                );
            }
        }
    }

    /**
     * For /tickets UI: sold out, ~90% warning, or unlimited (no cap).
     *
     * @param list<int> $ticketDetailsIds
     * @return array<int, array{sold_out: bool, nearly: bool, low_stock: bool, remaining: ?int}>
     */
    public function stockUiByTicketDetailsIds(array $ticketDetailsIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ticketDetailsIds))));
        $out = [];
        foreach ($ids as $id) {
            if ($id <= 0) {
                continue;
            }
            $capacity = $this->cartRepository->getTicketDetailsCapacity($id);
            if ($capacity === null) {
                $out[$id] = ['sold_out' => false, 'nearly' => false, 'low_stock' => false, 'remaining' => null];
                continue;
            }
            $sold = $this->ticketRepository->countSoldForTicketDetails($id);
            $reserved = $this->cartRepository->sumActiveCartQuantityForTicketDetails($id);
            $pending = $this->cartRepository->sumPendingOrderQuantityForTicketDetails($id);
            $used = $sold + $reserved + $pending;
            $remaining = max(0, $capacity - $used);
            $threshold = (int) max(1, floor(0.9 * $capacity));
            // Show "Only N left" for tight caps (demo) or when truly few remain (≤3).
            $tightCap = $capacity > 0 && $capacity <= 12;
            $out[$id] = [
                'sold_out' => $remaining <= 0,
                'nearly' => $capacity > 0 && $used >= $threshold && $remaining > 0,
                'low_stock' => $remaining > 0 && ($remaining <= 3 || $tightCap),
                'remaining' => $remaining,
            ];
        }

        return $out;
    }
}
