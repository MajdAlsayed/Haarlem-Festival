<?php

namespace App\Models;

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
    /** @var string|null Homepage card image filename (from event_types.card_image) */
    public ?string $cardImage = null;
    /** @var string|null INFO link path (from event_types.info_path) */
    public ?string $infoPath = null;
}
