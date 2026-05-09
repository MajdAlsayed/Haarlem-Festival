<?php

namespace App\Controllers;

use App\Repositories\SettingsRepository;
use App\Services\DanceService;
use App\ViewModels\DanceViewModel;

/**
 * Public Dance landing (/dance): grouped events, CMS/config copy, featured strip; builds DanceViewModel for the index view.
 */
class DanceController
{
    private DanceService $danceService;
    private SettingsRepository $settingsRepository;

    /** Wire dance page dependencies (events + dance settings + global app settings). */
    public function __construct()
    {
        $this->danceService = new DanceService(
            new \App\Repositories\EventRepository(),
            new \App\Repositories\DanceSettingsRepository()
        );
        $this->settingsRepository = new SettingsRepository();
    }

    /** Build homepage-ready dance data and render /dance. */
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

        // Curated hero strip: first Saturday slot plus two Sunday cards (design choice, not all Saturday).
        $featuredEvents = array_merge(
            array_slice($saturdayEvents, 0, 1),
            array_slice($sundayEvents, 1, 2)
        );

        $breadcrumbs = [
            ['label' => (string) ($danceSettings['breadcrumb_home_label'] ?? ''), 'url' => '/'],
            ['label' => (string) ($danceSettings['breadcrumb_dance_label'] ?? ''), 'url' => null],
        ];

        $pageTitle = isset($danceSettings['dance_page_title']) && is_string($danceSettings['dance_page_title'])
            ? $danceSettings['dance_page_title']
            : '';

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
            $pageTitle
        );

        require __DIR__ . '/../Views/Dance/Index.php';
    }
}
