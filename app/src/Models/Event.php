<?php

namespace App\Models;

class Event
{
    public int $id;
    public string $category;
    public string $title;
    public string $description;
    public ?string $location;
}
