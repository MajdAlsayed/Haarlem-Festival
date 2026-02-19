<?php

namespace App\ViewModels;

use App\Models\HistoryImage;

class HistoryViewModel
{
    public function __construct(
        public array $blocks,
        public array $locations,
        public ?HistoryImage $heroImage = null,
        public array $primaryImages = [],
    ){
    }
}