<?php

namespace App\Models;

class HistoryImage
{
public int $id;
public int $historyLocationId;
public string $imageUrl;
public ?string $altText;
public bool $isPrimary;
public int $sortOrder;
}