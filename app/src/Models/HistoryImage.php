<?php

namespace App\Models;

class HistoryImage
{
public int $id;
public ?int $historyLocationId;
public ?int $page_id;
public ?int $event_id;
public string $imageUrl;
public ?string $altText;
public bool $isPrimary;
public int $sortOrder;
public string $imageType;
}