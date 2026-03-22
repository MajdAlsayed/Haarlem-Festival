<?php

namespace App\Contracts;

use App\Models\Page;

/** Contract for page-by-slug lookup (swap/mock). */
interface PageRepositoryInterface
{
    public function getBySlug(string $slug): ?Page;

    /** Admin/CMS: load page by slug (published or not). */
    public function findBySlugForAdmin(string $slug): ?Page;

    /** Admin/CMS: update title (e.g. homepage). */
    public function updateTitleBySlug(string $slug, string $title): bool;
}
