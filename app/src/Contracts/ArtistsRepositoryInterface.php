<?php

declare(strict_types=1);

namespace App\Contracts;

interface ArtistsRepositoryInterface
{
    public function getBySlug(string $slug): ?array;

    public function getPhotoFilenamesByArtistId(int $artistId): array;

    public function getAllOrdered(): array;
}
