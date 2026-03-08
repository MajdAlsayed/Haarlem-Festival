<?php

namespace App\Contracts;

use App\Models\Page;

/** Contract for page-by-slug lookup (swap/mock). */
interface PageRepositoryInterface
{
    public function getBySlug(string $slug): ?Page;
}
