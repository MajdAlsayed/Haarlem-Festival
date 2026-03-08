<?php

declare(strict_types=1);

namespace App\Models;

final class Restaurant
{
    public function __construct(
        public int $restaurantId,
        public string $name,
        public string $slug,
        public string $address,
        public string $type,
        public ?string $image,
        public int $sessions,
        public float $durationHours,
        public string $firstSession,
        public int $stars,
        public int $seats,
        public float $priceAdult,
        public float $priceKid,
        public int $kidAgeMax,
        public ?int $walkMinutesToPatronaat
    ) {}
}