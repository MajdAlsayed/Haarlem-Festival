<?php

namespace App\ViewModels;

use App\Models\HistoryTour;
use App\Models\HistoryImage;

class HistoryToursViewModel
{
    public array $hero = [];
    public array $infoCards = [];

    public array $tourDetails = [];
    public array $ticketOptions= [];


    public function __construct(
        public array         $toursByDay,
        public array         $blocks,
        public ?HistoryImage $heroImage,
        public array            $locations = [],
    ){
        $this->hero = $this->blocks['hero']['content'] ?? [];
        $this->infoCards = $this->blocks['info_cards']['content'] ?? [];
        $this->tourDetails = $this->blocks['tour_details']['content'] ?? [];
        $this->ticketOptions = $this->blocks['ticket_options']['content'] ?? [];

        // Add language flags to tours
        foreach ($this->toursByDay as $date => $tours) {
            $this->toursByDay[$date] = $this->addLanguageFlag($tours);
        }
    }

    private function addLanguageFlag(array $tours): array
    {
        return array_map(function($tour) {
            $tour['flag'] = match($tour['language_name']) {
                'English' => '🇬🇧',
                'Dutch'   => '🇳🇱',
                'Chinese' => '🇨🇳',
                default   => ''
            };
            return $tour;
        }, $tours);
    }
}