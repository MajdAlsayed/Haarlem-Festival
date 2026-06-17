<?php

declare(strict_types=1);

namespace App\Contracts;

interface PhotosRepositoryInterface
{
    public function getFilename(string $context, ?string $key = null): ?string;
}
