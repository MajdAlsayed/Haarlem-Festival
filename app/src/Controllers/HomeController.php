<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Exceptions\NotFoundException;
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

        $categories = $this->eventTypeRepository->getAllWithDisplay();

        $cmsHome = (new SettingsRepository())->getMergedCmsHome();
        $viewModel = new HomeViewModel($page, $categories, $cmsHome);

        require __DIR__ . '/../Views/Home/Index.php';
    }
}
