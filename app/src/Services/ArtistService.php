<?php

namespace App\Services;

use App\Contracts\ArtistsRepositoryInterface;
use App\Contracts\EventRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Models\Event;

/** Artist page data: artist + gallery + events. Uses artists + event repos. */
class ArtistService
{
    public function __construct(
        private ArtistsRepositoryInterface $artistsRepository,
        private EventRepositoryInterface $eventRepository
    ) {
    }

    /** @return array{artist: array, galleryImages: list<string>, artistEvents: Event[]} */
    public function getArtistDetail(string $slug): array
    {
        $artist = $this->artistsRepository->getBySlug($slug);
        if (!$artist) {
            throw new NotFoundException('Artist not found');
        }

        $galleryImages = $this->artistsRepository->getPhotoFilenamesByArtistId($artist['id']);

        $allEvents = $this->eventRepository->getByCategory('dance');
        $searchName = $artist['slug'] === 'hardwell' ? 'Hardwell' : $artist['name']; // event titles use "Hardwell"
        $artistEvents = array_filter($allEvents, fn (Event $e) => stripos($e->title ?? '', $searchName) !== false);

        return [
            'artist' => $artist,
            'galleryImages' => $galleryImages,
            'artistEvents' => array_values($artistEvents),
        ];
    }

    /** @return array<int, array{name: string, slug: string|null, bio: string|null, image: string}> */
    public function getAllOrdered(): array
    {
        return $this->artistsRepository->getAllOrdered();
    }
}
