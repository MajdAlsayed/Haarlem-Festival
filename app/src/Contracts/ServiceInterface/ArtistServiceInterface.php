<?php

namespace App\Contracts\ServiceInterface;

interface ArtistServiceInterface
{
    public function getBySlug(string $slug): ?array;

    /** @return string[] */
    public function getPhotoFilenames(int $artistId): array;

    /** @return array[] */
    public function getAllOrdered(): array;
}
