<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\TicketsRepository;
use App\Services\TicketAvailabilityService;

/** Category tabs for /tickets; merges real-time stock hints into each ticket row for badges and disabling buy. */
final class TicketsController
{
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
        // One batch query worth of ids → sold_out / nearly / low_stock for the whole page (includes cart + pay-later holds).
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
     * @param list<array<string,mixed>> $items
     * @param array<int, array{sold_out: bool, nearly: bool, low_stock: bool, remaining: ?int}> $stock
     * @return list<array<string,mixed>>
     */
    private function attachStock(array $items, array $stock): array
    {
        foreach ($items as &$row) {
            $tid = (int) ($row['ticket_details_id'] ?? 0);
            // Passes without a cap get a neutral stock row so the template always reads $row['stock'].
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
