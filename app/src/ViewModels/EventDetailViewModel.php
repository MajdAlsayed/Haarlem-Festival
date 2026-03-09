<?php

namespace App\ViewModels;

use App\Models\Event;

class EventDetailViewModel
{
    public function __construct(
        public Event $event,
        public string $pageTitle = ''
    ) {
        if ($this->pageTitle === '') {
            $this->pageTitle = $this->event->venueName . ' — ' . $this->event->title;
        }
    }

    public function getFormattedDate(): string
    {
        if (!$this->event->eventDay) {
            return '';
        }
        $dayLabels = [
            'thursday' => 'Thursday 23 July',
            'friday' => 'Friday 24 July',
            'saturday' => 'Saturday 25 July',
            'sunday' => 'Sunday 26 July',
        ];
        return $dayLabels[strtolower($this->event->eventDay)] ?? ucfirst($this->event->eventDay);
    }

    public function getFormattedTime(): string
    {
        $start = $this->event->startTime ?? '19:00';
        return $start . ' - 01:00';
    }
}
