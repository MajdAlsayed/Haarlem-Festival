<?php

namespace App\Models;

class HistoryLocation
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description1;
    public ?string $description2;
    public ?int $pageId;
    public int $sortOrder;
    public ?string $shortDescription = null;
}