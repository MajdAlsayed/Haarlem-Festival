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
    public ?float $contributionTotal = null;

    public ?int $eventId = null;
    public ?string $eventTitle = null;
    public ?string $eventDay = null;
    public ?string $startTime = null;

    /** Unit price × quantity — what this line adds to the cart total. */
    public function getLineTotal(): float
    {
        // Pay-as-you-like stores the chosen total instead of using a fixed ticket price.
        if ($this->contributionTotal !== null) {
            return $this->contributionTotal;
        }

        return $this->price * $this->quantity;
    }

    public function isPayAsYouLike(): bool
    {
        return $this->contributionTotal !== null;
    }
}
