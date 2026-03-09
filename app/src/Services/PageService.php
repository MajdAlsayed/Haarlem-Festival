<?php

namespace App\Services;

use App\Contracts\PageRepositoryInterface;
use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Models\Page;
use App\Validation\Validator;

/** Page by slug; normalizes + validates then repo. */
class PageService implements PageServiceInterface
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {
    }

    public function getBySlug(string $slug): ?Page
    {
        $normalized = strtolower(trim($slug));
        Validator::validateSlug($normalized);
        return $this->pageRepository->getBySlug($normalized);
    }
}
