<?php

namespace App\ViewModels;

use App\Models\HistoryLocation;
use App\Models\HistoryImage;

class HistoryLocationViewModel
{
    // Parsed block properties
    public ?array $hero = null;
    public ?array $aboutBanner = null;
    public ?array $statsBar = null;
    public array $contentSections = [];
    public ?array $experience = null;

    public function __construct(
        public array            $blocks,
        public HistoryLocation  $location,
        public ?HistoryImage    $heroImage = null,
        public array            $contentImages = [],
        public ?HistoryLocation $prevLocation = null,
        public ?HistoryLocation $nextLocation = null,
    ){
        // Parse blocks into individual properties
        foreach ($this->blocks as $block) {
            switch ($block['block_type']) {
                case 'hero':
                    $this->hero = $block['content'];
                    break;
                case 'about_banner':
                    $this->aboutBanner = $block['content'];
                    break;
                case 'stats_bar':
                    $this->statsBar = $block['content'];
                    break;
                case 'content_section':
                    $this->contentSections[] = $block['content'];
                    break;
                case 'experience':
                    $this->experience = $block['content'];
                    break;
            }
        }
    }
}