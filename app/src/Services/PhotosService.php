<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PhotosRepositoryInterface;
use App\Contracts\ServiceInterface\PhotosServiceInterface;

// site_photos lookups for dance pages
final class PhotosService implements PhotosServiceInterface
{
    public function __construct(
        private PhotosRepositoryInterface $photosRepository,
    ) {
    }

    public function getFilename(string $context, ?string $key = null): ?string
    {
        return $this->photosRepository->getFilename($context, $key);
    }
}
