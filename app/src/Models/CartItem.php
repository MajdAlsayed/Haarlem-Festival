<?php

namespace App\Models;

/**
 * One row in the shopping basket — populated from `cart_items` joined with `ticket_details` (+ event fields for display).
 */
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

    /** Unit price × quantity — what this line adds to the cart total. */
    public function getLineTotal(): float
    {
        return $this->price * $this->quantity;
    }
}