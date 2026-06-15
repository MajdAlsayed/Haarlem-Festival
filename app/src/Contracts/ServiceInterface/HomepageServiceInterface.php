<?php

namespace App\Contracts\ServiceInterface;

use App\ViewModels\HomeViewModel;

interface HomepageServiceInterface
{
    public function buildIndexViewModel(): HomeViewModel;
}
