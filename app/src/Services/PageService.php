<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\PageRepositoryInterface;
use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Models\Page;
use App\Validation\Validator;

// page by slug — homepage title etc
class PageService implements PageServiceInterface
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {
    }

    // pages table lookup by url slug
    public function getBySlug(string $slug): ?Page
    {
        $normalized = strtolower(trim($slug));
        Validator::validateSlug($normalized);
        return $this->pageRepository->getBySlug($normalized);
    }

    public function findBySlugForAdmin(string $slug): ?Page
    {
        return $this->pageRepository->findBySlugForAdmin($slug);
    }

    public function updateTitleBySlug(string $slug, string $title): bool
    {
        return $this->pageRepository->updateTitleBySlug($slug, $title);
    }
}
