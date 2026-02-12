<?php

namespace App\Models;

class Event
{
    public int $id;

    public int $eventTypeId;
    public int $venueId;

    public string $title;
    public ?string $description = null;

    public string $eventTypeName;
    public string $venueName;
}
