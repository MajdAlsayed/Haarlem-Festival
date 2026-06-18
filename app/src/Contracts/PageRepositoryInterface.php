<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Page;

interface PageRepositoryInterface
{
    public function getBySlug(string $slug): ?Page;

    public function findBySlugForAdmin(string $slug): ?Page;

    public function updateTitleBySlug(string $slug, string $title): bool;
}
