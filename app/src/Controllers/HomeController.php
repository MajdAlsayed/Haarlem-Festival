<?php

namespace App\Controllers;

use App\Repositories\PageRepository;
use App\Repositories\EventRepository;

class HomeController
{
    private PageRepository $pageRepository;
    private EventRepository $eventRepository;

    public function __construct()
    {
        $this->pageRepository = new PageRepository();
        $this->eventRepository = new EventRepository();
    }

    public function index(): void
    {
        $page = $this->pageRepository->getBySlug('home');
        $events = $this->eventRepository->getAll();

        if ($page === null) {
            http_response_code(404);
            echo 'Homepage not found';
            return;
        }

        require __DIR__ . '/../Views/Home/Index.php';
    }
}
