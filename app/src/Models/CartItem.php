<?php

namespace App\Models;

class CartItem
{
    public int $cartItemId;
    public int $cartId;
    public int $ticketDetailsId;
    public int $quantity;

    public string $name;
    public string $description;
    public string $ticketType;
    public float $price;

    public ?int $eventId = null;
    public ?string $eventTitle = null;
    public ?string $eventDay = null;
    public ?string $startTime = null;

    public function getLineTotal(): float
    {
        return $this->price * $this->quantity;
    }
}