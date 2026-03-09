<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Exceptions\NotFoundException;
use App\Repositories\EventTypeRepository;
use App\Repositories\PageRepository;
use App\Services\PageService;
use App\ViewModels\HomeViewModel;

class HomeController
{
    private PageServiceInterface $pageService;
    private EventTypeRepository $eventTypeRepository;

    public function __construct()
    {
        $this->pageService = new PageService(new PageRepository());
        $this->eventTypeRepository = new EventTypeRepository();
    }

    public function index(): void
    {
        $page = $this->pageService->getBySlug('home');
        if ($page === null) {
            throw new NotFoundException('Homepage not found');
        }

        $categories = $this->eventTypeRepository->getAllWithDisplay();

        $viewModel = new HomeViewModel($page, $categories);

        require __DIR__ . '/../Views/Home/Index.php';
    }
}
