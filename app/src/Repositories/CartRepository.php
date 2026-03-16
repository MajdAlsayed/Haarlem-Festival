<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Cart;
use App\Models\CartItem;
use PDO;

class CartRepository
{
    public function findActiveCartByUserId(int $userId): ?Cart
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT cart_id, user_id, status, created_at
             FROM carts
             WHERE user_id = :user_id AND status = 'active'
             ORDER BY cart_id DESC
             LIMIT 1"
        );

        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToCart($row) : null;
    }

    public function findActiveCartById(int $cartId): ?Cart
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT cart_id, user_id, status, created_at
             FROM carts
             WHERE cart_id = :cart_id AND status = 'active'
             LIMIT 1"
        );

        $stmt->execute(['cart_id' => $cartId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToCart($row) : null;
    }

    public function createCart(?int $userId): int
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "INSERT INTO carts (user_id, status, created_at)
             VALUES (:user_id, 'active', NOW())"
        );

        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int)$db->lastInsertId();
    }

    public function attachCartToUser(int $cartId, int $userId): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "UPDATE carts
             SET user_id = :user_id
             WHERE cart_id = :cart_id"
        );

        $stmt->execute([
            'user_id' => $userId,
            'cart_id' => $cartId,
        ]);
    }

    public function findCartItem(int $cartId, int $ticketDetailsId): ?array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT cart_item_id, cart_id, ticket_details_id, quantity
             FROM cart_items
             WHERE cart_id = :cart_id AND ticket_details_id = :ticket_details_id
             LIMIT 1"
        );

        $stmt->execute([
            'cart_id' => $cartId,
            'ticket_details_id' => $ticketDetailsId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function addCartItem(int $cartId, int $ticketDetailsId, int $quantity): int
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "INSERT INTO cart_items (cart_id, ticket_details_id, quantity)
             VALUES (:cart_id, :ticket_details_id, :quantity)"
        );

        $stmt->execute([
            'cart_id' => $cartId,
            'ticket_details_id' => $ticketDetailsId,
            'quantity' => $quantity,
        ]);

        return (int)$db->lastInsertId();
    }

    public function incrementCartItem(int $cartItemId, int $quantity): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "UPDATE cart_items
             SET quantity = quantity + :quantity
             WHERE cart_item_id = :cart_item_id"
        );

        $stmt->execute([
            'quantity' => $quantity,
            'cart_item_id' => $cartItemId,
        ]);
    }

    public function updateCartItemQuantity(int $cartItemId, int $quantity): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "UPDATE cart_items
             SET quantity = :quantity
             WHERE cart_item_id = :cart_item_id"
        );

        $stmt->execute([
            'quantity' => $quantity,
            'cart_item_id' => $cartItemId,
        ]);
    }

    public function deleteCartItem(int $cartItemId): void
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "DELETE FROM cart_items
             WHERE cart_item_id = :cart_item_id"
        );

        $stmt->execute(['cart_item_id' => $cartItemId]);
    }

    public function ticketDetailsExists(int $ticketDetailsId): bool
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT ticket_details_id
             FROM ticket_details
             WHERE ticket_details_id = :ticket_details_id
             LIMIT 1"
        );

        $stmt->execute(['ticket_details_id' => $ticketDetailsId]);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return CartItem[]
     */
    public function getCartItemsDetailed(int $cartId): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            "SELECT
                ci.cart_item_id,
                ci.cart_id,
                ci.ticket_details_id,
                ci.quantity,
                td.name,
                td.description,
                td.ticket_type,
                td.price,
                td.event_id,
                e.title AS event_title,
                e.event_day,
                e.start_time
             FROM cart_items ci
             JOIN ticket_details td ON td.ticket_details_id = ci.ticket_details_id
             LEFT JOIN events e ON e.event_id = td.event_id
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.cart_item_id DESC"
        );

        $stmt->execute(['cart_id' => $cartId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->mapRowToCartItem($row);
        }

        return $items;
    }

    private function mapRowToCart(array $row): Cart
    {
        $cart = new Cart();
        $cart->cartId = (int)$row['cart_id'];
        $cart->userId = isset($row['user_id']) ? (int)$row['user_id'] : null;
        $cart->status = (string)$row['status'];
        $cart->createdAt = isset($row['created_at']) ? (string)$row['created_at'] : null;

        return $cart;
    }

    private function mapRowToCartItem(array $row): CartItem
    {
        $item = new CartItem();
        $item->cartItemId = (int)$row['cart_item_id'];
        $item->cartId = (int)$row['cart_id'];
        $item->ticketDetailsId = (int)$row['ticket_details_id'];
        $item->quantity = (int)$row['quantity'];
        $item->name = (string)$row['name'];
        $item->description = (string)$row['description'];
        $item->ticketType = (string)$row['ticket_type'];
        $item->price = (float)$row['price'];
        $item->eventId = isset($row['event_id']) ? (int)$row['event_id'] : null;
        $item->eventTitle = $row['event_title'] ?? null;
        $item->eventDay = $row['event_day'] ?? null;
        $item->startTime = $row['start_time'] ?? null;

        return $item;
    }
}