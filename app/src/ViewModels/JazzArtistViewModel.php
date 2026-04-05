<?php
declare(strict_types=1);

namespace App\ViewModels;

final class JazzArtistViewModel
{
    /**
     * @param array<int, array<string,mixed>> $events
     * @param list<array<string,mixed>> $discography track rows with image_url, audio_url, title, etc.
     */
    public function __construct(
        public string $slug,
        public string $artistTitle,
        public string $tagline,
        public string $heroImage,
        public string $bio,
        /** @var array<int, array<string,mixed>> $events */
        public array $events,
        /** @var list<array<string,mixed>> */
        public array $discography = []
    ) {}
}