<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\EventTypeRepository;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;
use App\Services\EventTypeService;
use App\Services\HomepageService;
use App\Services\PageService;
use App\Services\SettingsService;

// festival homepage (/)
final class HomeController
{
    private HomepageService $homepageService;

    public function __construct()
    {
        $this->homepageService = new HomepageService(
            new PageService(new PageRepository()),
            new EventTypeService(new EventTypeRepository()),
            new SettingsService(new SettingsRepository()),
        );
    }

    public function index(): void
    {
        $vm = $this->homepageService->buildIndexViewModel();

        require __DIR__ . '/../Views/Home/Index.php';
    }
}
