<?php

namespace App\Contracts\ServiceInterface;

interface ArtistServiceInterface
{
    public function getBySlug(string $slug): ?array;

    public function getPhotoFilenames(int $artistId): array;

    public function getAllOrdered(): array;
}
