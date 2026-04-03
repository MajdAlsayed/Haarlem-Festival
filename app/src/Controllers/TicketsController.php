<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketsRepository;

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
        $app = (new SettingsRepository())->getAll();
        $cartFlash = Session::getFlash('cart_success');
        $cartFlashError = Session::getFlash('cart_error');

        require __DIR__ . '/../Views/Tickets/index.php';
    }
}
