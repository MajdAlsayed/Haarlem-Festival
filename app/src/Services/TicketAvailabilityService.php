<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\TicketRepository;
use App\ViewModels\CartViewModel;
use InvalidArgumentException;
use RuntimeException;

// seat math — sold + cart + pending vs catalog capacity
final class TicketAvailabilityService
{
    private const SINGLE_TICKET_SHARE = 0.9;
    private const MIN_CAPACITY_FOR_SINGLE_RULE = 10;
    private const TIGHT_CAP_MAX = 12;
    private const LOW_STOCK_REMAINING = 3;

    public function __construct(
        private CartRepository $cartRepository,
        private TicketRepository $ticketRepository,
    ) {
    }

    // cart add/update — throws if delta would oversell
    public function assertDeltaAllowed(int $ticketDetailsId, int $delta): void
    {
        if ($delta <= 0) {
            return;
        }

        $capacity = $this->capacityFor($ticketDetailsId);
        if ($capacity === null) {
            return;
        }

        $used = $this->usedSeats($ticketDetailsId);

        if ($delta === 1 && $capacity > self::MIN_CAPACITY_FOR_SINGLE_RULE) {
            $maxSingleShare = $this->singleTicketCap($capacity);
            if ($used + $delta > $maxSingleShare) {
                throw new InvalidArgumentException(
                    'Single-ticket sales are limited to 90% of capacity for this event. '
                    . 'Add more than one ticket in one go, or choose another option.',
                );
            }
        }

        if ($used + $delta > $capacity) {
            throw new InvalidArgumentException($this->capacityErrorMessage($capacity, $used));
        }
    }

    // checkout — re-check every line before payment
    public function assertCartCanCheckout(CartViewModel $cart): void
    {
        foreach ($cart->items as $item) {
            $capacity = $this->capacityFor($item->ticketDetailsId);
            if ($capacity === null) {
                continue;
            }

            if ($this->usedSeats($item->ticketDetailsId) > $capacity) {
                throw new RuntimeException(
                    'Availability changed while you were checking out. Please return to your cart and adjust quantities.',
                );
            }
        }
    }

    // sold + cart + pending vs capacity
    public function stockUiByTicketDetailsIds(array $ticketDetailsIds): array
    {
        $stock = [];
        foreach ($this->normalizeIds($ticketDetailsIds) as $id) {
            $capacity = $this->capacityFor($id);
            if ($capacity === null) {
                $stock[$id] = $this->unlimitedStockState();
                continue;
            }

            $used = $this->usedSeats($id);
            $stock[$id] = $this->buildStockState($capacity, $used);
        }

        return $stock;
    }

    private function normalizeIds(array $ticketDetailsIds): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $ticketDetailsIds),
            static fn (int $id): bool => $id > 0,
        )));
    }

    private function capacityFor(int $ticketDetailsId): ?int
    {
        return $this->cartRepository->getTicketDetailsCapacity($ticketDetailsId);
    }

    private function usedSeats(int $ticketDetailsId): int
    {
        return $this->ticketRepository->countSoldForTicketDetails($ticketDetailsId)
            + $this->cartRepository->sumActiveCartQuantityForTicketDetails($ticketDetailsId)
            + $this->cartRepository->sumPendingOrderQuantityForTicketDetails($ticketDetailsId);
    }

    private function singleTicketCap(int $capacity): int
    {
        return (int) max(0, floor(self::SINGLE_TICKET_SHARE * $capacity));
    }

    private function nearlyThreshold(int $capacity): int
    {
        return (int) max(1, floor(self::SINGLE_TICKET_SHARE * $capacity));
    }

    private function capacityErrorMessage(int $capacity, int $used): string
    {
        $remaining = max(0, $capacity - $used);

        return $remaining > 0
            ? "Only {$remaining} ticket(s) left for this option."
            : 'This ticket option is sold out.';
    }

    private function unlimitedStockState(): array
    {
        return [
            'sold_out' => false,
            'nearly' => false,
            'low_stock' => false,
            'remaining' => null,
        ];
    }

    private function buildStockState(int $capacity, int $used): array
    {
        $remaining = max(0, $capacity - $used);
        $threshold = $this->nearlyThreshold($capacity);
        $tightCap = $capacity > 0 && $capacity <= self::TIGHT_CAP_MAX;

        return [
            'sold_out' => $remaining <= 0,
            'nearly' => $capacity > 0 && $used >= $threshold && $remaining > 0,
            'low_stock' => $remaining > 0 && ($remaining <= self::LOW_STOCK_REMAINING || $tightCap),
            'remaining' => $remaining,
        ];
    }
}
