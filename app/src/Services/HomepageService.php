<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\EventTypeServiceInterface;
use App\Contracts\ServiceInterface\HomepageServiceInterface;
use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\EventType;
use App\Models\Page;
use App\ViewModels\HomeViewModel;

// public / — category cards + cms strings from settings
class HomepageService implements HomepageServiceInterface
{
    private const HOME_SLUG = 'home';
    private const CARD_ORDER = ['dance', 'jazz', 'history', 'yammy', 'stories'];

    public function __construct(
        private PageServiceInterface $pageService,
        private EventTypeServiceInterface $eventTypeService,
        private SettingsServiceInterface $settingsService,
    ) {
    }

    // HomeController only needs this
    public function buildIndexViewModel(): HomeViewModel
    {
        return HomeViewModel::fromPageData($this->getHomepageData());
    }

    // home page row + category cards + cms strings
    public function getHomepageData(): array
    {
        return [
            'page' => $this->loadPage(),
            'categories' => $this->loadCategoryCards(),
            'cmsHome' => $this->loadCmsData(),
        ];
    }

    private function loadPage(): Page
    {
        $page = $this->pageService->getBySlug(self::HOME_SLUG);
        if ($page === null) {
            throw new NotFoundException('Homepage not found');
        }

        return $page;
    }

    private function loadCategoryCards(): array
    {
        try {
            $categories = $this->eventTypeService->getAllWithDisplay();
        } catch (\Throwable) {
            $categories = [];
        }

        if ($categories === []) {
            return $this->fallbackCategoryCards();
        }

        return $categories;
    }

    private function loadCmsData(): array
    {
        return $this->settingsService->getMergedCmsHome();
    }

    private function fallbackCategoryCards(): array
    {
        $config = require __DIR__ . '/../Config/events.php';
        $cardImages = $this->configList($config, 'card_images');
        $infoPaths = $this->configList($config, 'info_paths');
        $blurbs = $this->categoryBlurbs();

        $cards = [];
        $id = 1;
        foreach (self::CARD_ORDER as $slug) {
            if (!isset($cardImages[$slug])) {
                continue;
            }

            $card = new EventType();
            $card->id = $id++;
            $card->name = $slug;
            $card->description = $blurbs[$slug];
            $card->cardImage = $cardImages[$slug];
            $card->infoPath = $this->normalizePublicPath(
                isset($infoPaths[$slug]) ? (string) $infoPaths[$slug] : '#',
            );
            $cards[] = $card;
        }

        return $cards;
    }

    private function categoryBlurbs(): array
    {
        return [
            'dance' => 'Electronic nights and headline DJs across Haarlem.',
            'jazz' => 'Jazz in clubs and on outdoor stages.',
            'history' => 'Guided walks and heritage routes.',
            'yammy' => 'Food trail and tasting experiences.',
            'stories' => 'Story venues and immersive routes.',
        ];
    }

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

    private function configList(array $config, string $key): array
    {
        if (isset($config[$key]) && is_array($config[$key])) {
            return $config[$key];
        }

        return [];
    }
}
