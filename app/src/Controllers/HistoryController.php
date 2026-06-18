<?php

declare(strict_types=1);

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
        try {
            // Get page blocks
            $result = $this->historyService->getPageBlocks('history');
            $blocks = $result['blocks'];

            // Get hero image (if no - return null)
            $heroImageId = $blocks['hero']['content']['image_id'] ?? null;
            $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

            // Get locations and their primary images from service
            $locationsData = $this->historyService->getLocationsWithImages($blocks);

            $viewModel = new HistoryViewModel(
                blocks: $blocks,
                locations: $locationsData['locations'],
                heroImage: $heroImage,
                primaryImages: $locationsData['primaryImages']
            );

            require __DIR__ . '/../Views/History/Index.php';
        } catch (\Exception $e) {
            error_log('HistoryController::index error: ' . $e->getMessage());
            require __DIR__ . '/../Views/error.php';
        }
    }

    public function locations(): void
    {
        try {
            // Get page blocks
            $result = $this->historyService->getPageBlocks('history-locations');
            $blocks = $result['blocks'];

            // Get hero image (if no - return null)
            $heroImageId = $blocks['hero']['content']['image_id'] ?? null;
            $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

            // Get locations and their primary images from service
            $locationsData = $this->historyService->getLocationsWithImages($blocks);

            $viewModel = new HistoryViewModel(
                blocks: $blocks,
                locations: $locationsData['locations'],
                heroImage: $heroImage,
                primaryImages: $locationsData['primaryImages']
            );

            require __DIR__ . '/../Views/History/locations.php';
        } catch (\Exception $e) {
            error_log('HistoryController::locations error: ' . $e->getMessage());
            require __DIR__ . '/../Views/error.php';
        }
    }

    public function show(string $slug): void
    {
        try {
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

            require __DIR__ . '/../Views/History/location.php';
        } catch (NotFoundException $e) {
            // Location not found — show 404
            error_log('HistoryController::show not found: ' . $e->getMessage());
            http_response_code(404);
            require __DIR__ . '/../Views/error.php';
        } catch (\Exception $e) {
            error_log('HistoryController::show error: ' . $e->getMessage());
            require __DIR__ . '/../Views/error.php';
        }
    }

    public function tours(): void
    {
        try {
            // Get page blocks
            $result = $this->historyService->getPageBlocks('history-tours');
            $blocks = $result['blocks'];

            // Get hero image (if no - return null)
            $heroImageId = $blocks['hero']['content']['image_id'] ?? null;
            $heroImage = $heroImageId ? $this->historyService->getImageById($heroImageId) : null;

            // Get all available dates and tours per day
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

            require __DIR__ . '/../Views/History/tours.php';
        } catch (\Exception $e) {
            error_log('HistoryController::tours error: ' . $e->getMessage());
            require __DIR__ . '/../Views/error.php';
        }
    }
}