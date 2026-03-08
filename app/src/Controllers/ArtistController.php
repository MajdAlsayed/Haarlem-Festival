<?php

namespace App\Controllers;

use App\Repositories\ArtistsRepository;
use App\Repositories\EventRepository;
use App\Services\ArtistService;

class ArtistController
{
    private ArtistService $artistService;

    public function __construct()
    {
        $this->artistService = new ArtistService(
            new ArtistsRepository(),
            new EventRepository()
        );
    }

    public function show(string $slug): void
    {
        $data = $this->artistService->getArtistDetail($slug);
        $artist = $data['artist'];
        $galleryImages = $data['galleryImages'];
        $artistEvents = $data['artistEvents'];

        require __DIR__ . '/../Views/Dance/ArtistDetail.php';
    }
}
