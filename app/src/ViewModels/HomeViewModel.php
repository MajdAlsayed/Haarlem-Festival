<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\EventType;
use App\Models\Page;

class HomeViewModel
{

    public function __construct(
        public Page $page,
        public array $categories,
        public array $cmsHome = [],
    ) {
    }

    public static function fromPageData(array $page): self
    {
        return new self(
            page: $page['page'],
            categories: $page['categories'],
            cmsHome: $page['cmsHome'],
        );
    }
}
