<?php

namespace App\ViewModels;

use App\Models\HistoryImage;

class HistoryViewModel
{
    // Parsed block properties
    public array $hero = [];
    public array $aboutBanner = [];
    public array $sitesHeader = [];
    public array $locationCards = [];
    public array $experience = [];

    public function __construct(
        public array         $blocks,
        public array         $locations,
        public ?HistoryImage $heroImage = null,
        public array         $primaryImages = [],
    ){

        $this->hero = $this->blocks['hero']['content'] ?? [];
        $this->aboutBanner = $this->blocks['about_banner']['content'] ?? [];
        $this->sitesHeader = $this->blocks['section_header']['content'] ?? [];
        $this->locationCards = $this->blocks['location_cards']['content'] ?? [];
        $this->experience = $this->blocks['text_block']['content'] ?? [];
    }
}