<?php

namespace App\Controllers;

use App\Repositories\SettingsRepository;
use App\Services\DanceService;
use App\ViewModels\DanceViewModel;

/**
 * Dance page. Controller gets data from services, builds the view model, then loads the view.
 */
class DanceController
{
    private DanceService $danceService;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->danceService = new DanceService(
            new \App\Repositories\EventRepository(),
            new \App\Repositories\DanceSettingsRepository()
        );
        $this->settingsRepository = new SettingsRepository();
    }

    public function index(): void
    {
        try {
            $grouped = $this->danceService->getEventsGroupedByDay();
            $artists = $this->danceService->getArtistsOrdered();
            $danceSettings = $this->danceService->getDanceSettings();
            $appSettings = $this->settingsRepository->getAll();
        } catch (\Throwable $e) {
            throw $e;
        }

        $fridayEvents = $grouped['friday'];
        $saturdayEvents = $grouped['saturday'];
        $sundayEvents = $grouped['sunday'];
        $events = $grouped['all'];

        $featuredEvents = array_merge(
            array_slice($saturdayEvents, 0, 1),
            array_slice($sundayEvents, 1, 2)
        );

        $breadcrumbs = [
            ['label' => 'HOME', 'url' => '/'],
            ['label' => 'DANCE', 'url' => null],
        ];

        $viewModel = new DanceViewModel(
            $events,
            $fridayEvents,
            $saturdayEvents,
            $sundayEvents,
            $featuredEvents,
            $artists,
            $appSettings,
            $danceSettings,
            $breadcrumbs,
            'Dance Festival'
        );

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
