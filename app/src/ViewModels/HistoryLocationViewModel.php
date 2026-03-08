<?php

namespace App\ViewModels;

use App\Models\HistoryLocation;
use App\Models\HistoryImage;

class HistoryLocationViewModel
{
    public function __construct(
        public array            $blocks,
        public HistoryLocation  $location,
        public ?HistoryImage    $heroImage = null,
        public array            $contentImages = [],
        public ?HistoryLocation $prevLocation = null,
        public ?HistoryLocation $nextLocation = null,
    ){
    }
}