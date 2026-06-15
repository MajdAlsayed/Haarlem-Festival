<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArtistsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;
use App\Repositories\SettingsRepository;
use App\Services\DanceService;

/** Public Dance landing (/dance). */
final class DanceController
{
    private DanceService $danceService;

    public function __construct()
    {
        $this->danceService = new DanceService(
            new EventRepository(),
            new DanceSettingsRepository(),
            new ArtistsRepository(),
            new SettingsRepository()
        );
    }

    public function index(): void
    {
        $vm = $this->danceService->buildIndexViewModel();

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
