<?php

namespace App\Models;

class EventType
{
    public int $id;
    public string $name;
    public string $description;
    public ?string $cardImage = null;
    public ?string $infoPath = null;
}
