<?php

namespace App\Models;

class HistoryLocation
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public ?int $pageId;
    public int $sortOrder;
}