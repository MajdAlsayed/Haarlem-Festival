<?php

namespace App\Contracts;

interface ArtistsRepositoryInterface
{
    /** @return array{id: int, name: string, slug: string|null, bio: string|null, image: string}|null */
    public function getBySlug(string $slug): ?array;

    /** @return list<string> */
    public function getPhotoFilenamesByArtistId(int $artistId): array;

    /** @return array<int, array{name: string, slug: string|null, bio: string|null, image: string}> */
    public function getAllOrdered(): array;
}
