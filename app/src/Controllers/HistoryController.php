<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\HistoryServiceInterface;
use App\Repositories\HistoryRepository;
use App\Services\HistoryService;
use App\ViewModels\HistoryViewModel;
use App\ViewModels\HistoryLocationViewModel;
use App\ViewModels\HistoryToursViewModel;
use App\Exceptions\NotFoundException;

class HistoryController
{
    private HistoryServiceInterface $historyService;

    public function __construct()
    {
        $this->historyService = new HistoryService(new HistoryRepository());
    }

    public function index(): void
    {

        // Get page blocks
        $result = $this->historyService->getPageBlocks('history');
        $blocks = $result['blocks'];

        // Get hero image (if no - return null)
        $heroImageId = $blocks['hero']['content']['image_id'] ?? null;
        $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

        // Get locations for location_cards block
        $locationIds = array_column(
            $blocks['location_cards']['content']['cards'] ?? [],
            'location_id'
        );
        $locations = array_filter(array_map(
            fn($id) => $this->historyService->getLocationById($id),
            $locationIds
        ));

        // Primary images for location cards
        $primaryImages = [];
        foreach ($locations as $location) {
            $primaryImages[$location->id] = $this->historyService->getPrimaryImage($location->id);
        }

        $viewModel = new HistoryViewModel(
            blocks: $blocks,
            locations: $locations,
            heroImage: $heroImage,
            primaryImages: $primaryImages
        );
        require __DIR__ . '/../Views/History/Index.php';
    }

    public function locations(): void
    {
        // Get page blocks
        $result = $this->historyService->getPageBlocks('history-locations');
        $blocks = $result['blocks'];

        // Get hero image (if no - return null)
        $heroImageId = $blocks['hero']['content']['image_id'] ?? null;
        $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

        // Get all locations from location_cards block
        $locationIds = array_column(
            $blocks['location_cards']['content']['cards'] ?? [],
            'location_id'
        );
        $locations = array_filter(array_map(
            fn($id) => $this->historyService->getLocationById($id),
            $locationIds
        ));

        // Get primary image for each location card
        $primaryImages = [];
        foreach ($locations as $location) {
            $primaryImages[$location->id] = $this->historyService->getPrimaryImage($location->id);
        }

        $viewModel = new HistoryViewModel(
            blocks: $blocks,
            locations: $locations,
            heroImage: $heroImage,
            primaryImages: $primaryImages
        );

        require __DIR__ . '/../Views/History/Locations.php';
    }

    public function show(string $slug): void
    {
        $location = $this->historyService->getLocationBySlug($slug);

        if ($location === null) {
            throw new NotFoundException('Location not found');
        }

        // Get page blocks
        $result = $this->historyService->getPageBlocksList($location->pageSlug);
        $blocks = $result['blocks'];

        $heroBlock = null;
        foreach ($blocks as $block) {
            if ($block['block_type'] === 'hero') {
                $heroBlock = $block['content'];
                break;
            }
        }

        $contentImages = [];
        foreach ($blocks as $block) {
            if ($block['block_type'] === 'content_section') {
                $imageIds = $block['content']['image_ids'] ?? [];
                foreach ($imageIds as $imageId) {
                    $contentImages[$imageId] = $this->historyService->getImageById($imageId);
                }
            }
        }

        $heroImageId = $heroBlock['image_id'] ?? null;
        $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

        // Get previous and next locations for navigation
        $prevLocation = $this->historyService->getLocationBySortOrder($location->sortOrder - 1);
        $nextLocation = $this->historyService->getLocationBySortOrder($location->sortOrder + 1);

        $viewModel = new HistoryLocationViewModel(
            blocks: $blocks,
            location: $location,
            heroImage: $heroImage,
            contentImages: $contentImages,
            prevLocation: $prevLocation,
            nextLocation: $nextLocation,
        );

        require __DIR__ . '/../Views/History/Location.php';
    }

    public function tours(): void
    {

        // Get page blocks
        $result = $this->historyService->getPageBlocks('history-tours');
        $blocks = $result['blocks'];

        // Get hero image (if no - return null)
        $heroImageId = $blocks['hero']['content']['image_id'] ?? null;
        $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

        // Get all available dates
        $dates = $this->historyService->getTourDates();
        $toursByDay = [];
        foreach ($dates as $date) {
            $toursByDay[$date] = $this->historyService->getToursWithDetailsByDate($date);
        }

        // Get all locations (for map)
        $locations = $this->historyService->getAllLocations();

        $viewModel = new HistoryToursViewModel(
            blocks: $blocks,
            heroImage: $heroImage,
            toursByDay: $toursByDay,
            locations: $locations,
        );

        require __DIR__ . '/../Views/History/Tours.php';
    }
    public function toursSchedule(): void
    {
        $date = $_GET['day'] ?? '';
        $tours = $this->historyService->getToursWithDetailsByDate($date);
        require __DIR__ . '/../Views/History/ToursSchedule.php';
    }
}