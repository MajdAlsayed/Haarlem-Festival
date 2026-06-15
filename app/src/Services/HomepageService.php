<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\HomepageServiceInterface;
use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\EventType;
use App\Repositories\EventTypeRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\HomeViewModel;

// builds the data for the public homepage (/)
class HomepageService implements HomepageServiceInterface
{
    public function __construct(
        private PageServiceInterface $pageService,
        private EventTypeRepository $eventTypeRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    // load the home page row, the event-type cards, and the cms copy into one view model
    public function buildIndexViewModel(): HomeViewModel
    {
        $page = $this->pageService->getBySlug('home');
        if ($page === null) {
            throw new NotFoundException('Homepage not found');
        }

        $categories = $this->loadEventTypeCards();
        $cmsHome = $this->settingsRepository->getMergedCmsHome();

        return new HomeViewModel($page, $categories, $cmsHome);
    }

    // event-type cards for the grid; fall back to config when the table is empty or unavailable
    /** @return EventType[] */
    private function loadEventTypeCards(): array
    {
        try {
            $categories = $this->eventTypeRepository->getAllWithDisplay();
        } catch (\Throwable) {
            // fresh clone or bad seed — recover with the config cards below
            $categories = [];
        }

        if ($categories === []) {
            return $this->fallbackEventTypes();
        }

        return $categories;
    }

    // five festival cards from config/events.php, used when the db has none
    /** @return EventType[] */
    private function fallbackEventTypes(): array
    {
        $config = require __DIR__ . '/../Config/events.php';
        $cardImages = $config['card_images'] ?? [];
        $infoPaths = $config['info_paths'] ?? [];

        $blurbs = [
            'dance' => 'Electronic nights and headline DJs across Haarlem.',
            'jazz' => 'Jazz in clubs and on outdoor stages.',
            'history' => 'Guided walks and heritage routes.',
            'yammy' => 'Food trail and tasting experiences.',
            'stories' => 'Story venues and immersive routes.',
        ];
        $order = ['dance', 'jazz', 'history', 'yammy', 'stories'];

        $cards = [];
        $id = 1;
        foreach ($order as $slug) {
            if (!isset($cardImages[$slug])) {
                continue;
            }

            $card = new EventType();
            $card->id = $id++;
            $card->name = $slug;
            $card->description = $blurbs[$slug] ?? '';
            $card->cardImage = $cardImages[$slug];
            $card->infoPath = $this->normalizePublicPath((string) ($infoPaths[$slug] ?? '#'));
            $cards[] = $card;
        }

        return $cards;
    }

    // make sure a card link is a usable site path (leading slash, or "#" when there's none)
    private function normalizePublicPath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '#') {
            return '#';
        }
        if ($path[0] === '/') {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }
}
