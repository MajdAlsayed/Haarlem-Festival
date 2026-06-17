<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

use App\ViewModels\HomeViewModel;

interface HomepageServiceInterface
{
    public function buildIndexViewModel(): HomeViewModel;

    public function getHomepageData(): array;
}
