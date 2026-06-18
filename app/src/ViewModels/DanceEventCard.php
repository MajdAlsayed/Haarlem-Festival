<?php

declare(strict_types=1);

namespace App\ViewModels;

final class DanceEventCard
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $imagePath,
        public string $genre,
        public string $venueLine,
        public string $whenLabel,
    ) {
    }
}
