<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArtistsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;
use App\Repositories\SettingsRepository;
use App\Services\ArtistService;
use App\Services\DanceService;
use App\Services\DanceSettingsService;
use App\Services\EventService;
use App\Services\SettingsService;

// public dance page — logic sits in DanceService
final class DanceController
{
    private DanceService $danceService;

    public function __construct()
    {
        $this->danceService = new DanceService(
            new EventService(new EventRepository()),
            new DanceSettingsService(new DanceSettingsRepository()),
            new ArtistService(new ArtistsRepository()),
            new SettingsService(new SettingsRepository()),
        );
    }

    // GET /dance
    public function index(): void
    {
        $vm = $this->danceService->buildIndexViewModel();

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
