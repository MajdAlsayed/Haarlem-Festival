<?php

namespace App\Controllers;

use App\Repositories\PageRepository;

class HomeController
{
    private PageRepository $pageRepository;

    public function __construct()
    {
        $this->pageRepository = new PageRepository();
    }

    public function index(): void
    {
        $page = $this->pageRepository->getBySlug('home');

        if ($page === null) {
            http_response_code(404);
            echo 'Homepage not found';
            return;
        }

        require __DIR__ . '/../Views/Home/Index.php';
    }
}
