<?php

namespace App\Contracts;

use App\Models\Page;

interface PageRepositoryInterface
{
    public function getBySlug(string $slug): ?Page;
}
