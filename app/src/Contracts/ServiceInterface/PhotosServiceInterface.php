<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface PhotosServiceInterface
{
    public function getFilename(string $context, ?string $key = null): ?string;
}
