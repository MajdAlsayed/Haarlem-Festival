<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArtistsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\EventRepository;
use App\Repositories\PhotosRepository;
use App\Repositories\SettingsRepository;
use App\Services\ArtistService;
use App\Services\DanceArtistService;
use App\Services\DanceSettingsService;
use App\Services\EventService;
use App\Services\PhotosService;
use App\Services\SettingsService;

// dance artist detail — /dance/artist/{slug}
final class ArtistController
{
    private DanceArtistService $danceArtistService;

    public function __construct()
    {
        $this->danceArtistService = new DanceArtistService(
            new ArtistService(new ArtistsRepository()),
            new EventService(new EventRepository()),
            new PhotosService(new PhotosRepository()),
            new SettingsService(new SettingsRepository()),
            new DanceSettingsService(new DanceSettingsRepository()),
        );
    }

    public function show(string $slug): void
    {
        $vm = $this->danceArtistService->buildDetailViewModel($slug);

        require __DIR__ . '/../Views/Dance/ArtistDetail.php';
    }
}
