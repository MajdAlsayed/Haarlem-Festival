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
use App\Services\EventService;

/** /dance/artist/{slug} */
final class ArtistController
{
    private DanceArtistService $danceArtistService;

    public function __construct()
    {
        $this->danceArtistService = new DanceArtistService(
            new ArtistService(new ArtistsRepository()),
            new EventService(new EventRepository()),
            new PhotosRepository(),
            new SettingsRepository(),
            new DanceSettingsRepository()
        );
    }

    public function show(string $slug): void
    {
        $vm = $this->danceArtistService->buildDetailViewModel($slug);

        require __DIR__ . '/../Views/Dance/ArtistDetail.php';
    }
}
