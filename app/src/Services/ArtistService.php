<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ArtistsRepositoryInterface;
use App\Contracts\ServiceInterface\ArtistServiceInterface;

// thin data access for artists — just talks to the repository
class ArtistService implements ArtistServiceInterface
{
    public function __construct(
        private ArtistsRepositoryInterface $artistsRepository
    ) {
    }

    // one artist by slug, or null if there isn't one
    public function getBySlug(string $slug): ?array
    {
        return $this->artistsRepository->getBySlug($slug);
    }

    // gallery photo filenames for an artist
    /** @return string[] */
    public function getPhotoFilenames(int $artistId): array
    {
        return $this->artistsRepository->getPhotoFilenamesByArtistId($artistId);
    }

    // every artist row in the repository's order
    /** @return array[] */
    public function getAllOrdered(): array
    {
        return $this->artistsRepository->getAllOrdered();
    }
}
