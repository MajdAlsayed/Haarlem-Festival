<?php

namespace App\Contracts\ServiceInterface;

use App\Models\Page;

interface PageServiceInterface
{
    public function getBySlug(string $slug): ?Page;
}
