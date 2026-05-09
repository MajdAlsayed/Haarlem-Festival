<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\TicketAvailabilityService;

/**
 * The public “Tickets” page people use to browse passes and single events.
 *
 * The ?cat= query switches the tab (jazz, dance, history, stories). We load rows from TicketsRepository, then
 * ask TicketAvailabilityService how tight capacity is — that’s what drives “Sold out”, “Only X left”, and dimming BUY.
 * After someone adds to cart, CartController may leave a one-time success/error message in the session; we show it at the top of the view.
 */
final class TicketsController
{
    /**
     * Renders the tickets page: intro line, special-offer passes, then Thursday–Sunday grids for that category.
     * Every card gets a `stock` array so the template doesn’t have to think about math.
     */
    public function index(): void
    {
        $repo = new TicketsRepository();
        $category = strtolower(trim((string) ($_GET['cat'] ?? 'jazz')));
        if (!in_array($category, TicketsRepository::CATEGORIES, true)) {
            $category = 'jazz';
        }

        $intro = $repo->getIntroText();
        $passes = $repo->getPassesForCategory($category);
        $byDay = $repo->getEventTicketsGroupedByDay($category);

        $ids = [];
        foreach ($passes as $p) {
            $ids[] = (int) ($p['ticket_details_id'] ?? 0);
        }
        foreach ($byDay as $rows) {
            foreach ($rows as $e) {
                $ids[] = (int) ($e['ticket_details_id'] ?? 0);
            }
        }
        // Figure out stock for every ticket on this page in one go (faster than asking per card).
        $cartRepo = new CartRepository();
        $stock = (new TicketAvailabilityService($cartRepo, new TicketRepository()))->stockUiByTicketDetailsIds($ids);
        $passes = $this->attachStock($passes, $stock);
        foreach (array_keys($byDay) as $day) {
            $byDay[$day] = $this->attachStock($byDay[$day], $stock);
        }

        $app = (new SettingsRepository())->getAll();
        $cartFlash = Session::getFlash('cart_success');
        $cartFlashError = Session::getFlash('cart_error');

        require __DIR__ . '/../Views/Tickets/index.php';
    }

    /**
     * Copies the precomputed stock info onto each row so the Blade/HTML side can always do $row['stock']['sold_out'] etc.
     *
     * @param list<array<string,mixed>> $items
     * @param array<int, array{sold_out: bool, nearly: bool, low_stock: bool, remaining: ?int}> $stock
     * @return list<array<string,mixed>>
     */
    private function attachStock(array $items, array $stock): array
    {
        foreach ($items as &$row) {
            $tid = (int) ($row['ticket_details_id'] ?? 0);
            // Day passes don’t have a seat cap — we still attach a “boring” stock row so the view doesn’t need if/else.
            $row['stock'] = $stock[$tid] ?? [
                'sold_out' => false,
                'nearly' => false,
                'low_stock' => false,
                'remaining' => null,
            ];
        }
        unset($row);

        return $items;
    }
}
