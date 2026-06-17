<?php

declare(strict_types=1);

namespace App\ViewModels;

final class DanceArtistCard
{
    public function __construct(
        public string $name,
        public string $bio,
        public string $imagePath,
        public string $url,
    ) {
    }
}
