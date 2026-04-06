<?php

namespace App\ViewModels;

use App\Models\CartItem;

class CartViewModel
{
    public ?int $cartId;
    public int $itemCount;
    public float $total;

    /** @var CartItem[] */
    public array $items;

    /**
     * @param CartItem[] $items
     */
    public function __construct(?int $cartId, array $items)
    {
        $this->cartId = $cartId;
        $this->items = $items;
        $this->itemCount = 0;
        $this->total = 0.0;

        foreach ($items as $item) {
            $this->itemCount += $item->quantity;
            $this->total += $item->getLineTotal();
        }
    }

    public function toArray(): array
    {
        return [
            'cart_id' => $this->cartId,
            'item_count' => $this->itemCount,
            'total' => round($this->total, 2),
            'items' => array_map(function (CartItem $item) {
                return [
                    'cart_item_id' => $item->cartItemId,
                    'ticket_details_id' => $item->ticketDetailsId,
                    'name' => $item->name,
                    'description' => $item->description,
                    'ticket_type' => $item->ticketType,
                    'price' => round($item->price, 2),
                    'quantity' => $item->quantity,
                    'line_total' => round($item->getLineTotal(), 2),
                    'event_id' => $item->eventId,
                    'event_title' => $item->eventTitle,
                    'event_day' => $item->eventDay,
                    'start_time' => $item->startTime,
                ];
            }, $this->items),
        ];
    }
}