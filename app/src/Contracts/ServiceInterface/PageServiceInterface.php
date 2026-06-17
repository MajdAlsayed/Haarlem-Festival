<?php

namespace App\Contracts\ServiceInterface;

use App\Models\Page;

interface PageServiceInterface
{
    public function getBySlug(string $slug): ?Page;

    public function findBySlugForAdmin(string $slug): ?Page;

    public function updateTitleBySlug(string $slug, string $title): bool;
}
