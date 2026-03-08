<?php

namespace App\Models;

class HistoryImage
{
    public int $id;
    public ?int $historyLocationId;
    public ?int $pageId;
    public ?int $eventId;
    public string $imageUrl;
    public ?string $altText;
    public bool $isPrimary;
    public int $sortOrder;
    public string $imageType;
}