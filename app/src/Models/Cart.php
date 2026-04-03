<?php

namespace App\Models;

class Cart
{
    public int $cartId;
    public ?int $userId = null;
    public string $status;
    public ?string $createdAt = null;
}