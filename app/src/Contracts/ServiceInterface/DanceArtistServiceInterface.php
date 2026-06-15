<?php

namespace App\Contracts\ServiceInterface;

use App\ViewModels\ArtistDetailViewModel;

interface DanceArtistServiceInterface
{
    public function buildDetailViewModel(string $slug): ArtistDetailViewModel;
}
