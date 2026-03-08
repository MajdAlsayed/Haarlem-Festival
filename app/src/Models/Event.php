<?php

namespace App\Models;

/** One event: events row + event_types + venues (from JOIN in repo). */
class Event
{
    public int $id;

    public int $eventTypeId;
    public int $venueId;

    public string $title;
    public ?string $description = null;

    /** @var string|null e.g. 'friday', 'saturday', 'sunday' */
    public ?string $eventDay = null;
    /** @var string|null e.g. '22:00', '14:00' */
    public ?string $startTime = null;

    public string $eventTypeName;
    public string $venueName;
    public string $venueCity;
    public ?string $venueAddress = null;
    /** @var string|null event_types.card_image */
    public ?string $cardImage = null;
    /** @var string|null event_types.info_path */
    public ?string $infoPath = null;
}
