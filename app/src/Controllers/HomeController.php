<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\EventType;
use App\Repositories\EventTypeRepository;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;
use App\Services\PageService;
use App\ViewModels\HomeViewModel;

/**
 * Public site homepage (/): loads the `home` page from the DB, event-type cards for the grid,
 * and merged CMS copy (hero, welcome, about) from `site_settings` + defaults in Config — same keys the admin homepage form edits.
 */
class HomeController
{
    private PageServiceInterface $pageService;
    private EventTypeRepository $eventTypeRepository;

    public function __construct()
    {
        $this->pageService = new PageService(new PageRepository());
        $this->eventTypeRepository = new EventTypeRepository();
    }

    /** Builds HomeViewModel and renders Views/Home/Index.php. */
    public function index(): void
    {
        $page = $this->pageService->getBySlug('home');
        if ($page === null) {
            throw new NotFoundException('Homepage not found');
        }

        try {
            $categories = $this->eventTypeRepository->getAllWithDisplay();
        } catch (\Throwable) {
            $categories = [];
        }
        if ($categories === []) {
            $categories = $this->fallbackHomepageEventTypes();
        }

        $cmsHome = (new SettingsRepository())->getMergedCmsHome();
        $viewModel = new HomeViewModel($page, $categories, $cmsHome);

        require __DIR__ . '/../Views/Home/Index.php';
    }

    /**
     * When event_types is empty or DB errors (fresh clone, bad seed), still render five festival cards from Config/events.php.
     *
     * @return EventType[]
     */
    private function fallbackHomepageEventTypes(): array
    {
        $cfg = require __DIR__ . '/../Config/events.php';
        $cardImages = $cfg['card_images'] ?? [];
        $infoPaths = $cfg['info_paths'] ?? [];
        $blurbs = [
            'dance' => 'Electronic nights and headline DJs across Haarlem.',
            'jazz' => 'Jazz in clubs and on outdoor stages.',
            'history' => 'Guided walks and heritage routes.',
            'yammy' => 'Food trail and tasting experiences.',
            'stories' => 'Story venues and immersive routes.',
        ];
        $order = ['dance', 'jazz', 'history', 'yammy', 'stories'];
        $out = [];
        $id = 1;
        foreach ($order as $slug) {
            if (!isset($cardImages[$slug])) {
                continue;
            }
            $t = new EventType();
            $t->id = $id++;
            $t->name = $slug;
            $t->description = $blurbs[$slug] ?? '';
            $t->cardImage = $cardImages[$slug];
            $t->infoPath = $this->normalizePublicPath((string) ($infoPaths[$slug] ?? '#'));
            $out[] = $t;
        }

        return $out;
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
}
