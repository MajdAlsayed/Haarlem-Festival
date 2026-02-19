<?php

namespace App\Controllers;

use App\Repositories\HistoryRepository;
use App\Services\HistoryService;
use App\ViewModels\HistoryViewModel;

class HistoryController
{
    private HistoryService $historyService;

    public function __construct()
    {
        $this->historyService = new HistoryService(new HistoryRepository());
    }

    public function index(): void
    {

        // Get page blocks
        $result = $this->historyService->getPageBlocks('history');
        $pageId = $result['page_id'];
        $blocks = $result['blocks'];

        // Get hero image
        $heroImage = $pageId
            ? $this->historyService->getPageHeroImage($pageId)
            : null;

        // Get locations for location cards block
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
}