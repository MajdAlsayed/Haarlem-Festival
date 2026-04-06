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
     * Capacity for this catalog row: session slot, event seats, or unlimited (passes / no cap).
     */
    public function getTicketDetailsCapacity(int $ticketDetailsId): ?int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT td.ticket_type, td.session_id, td.event_id,
                    s.tickets_available AS session_cap,
                    e.seats AS event_seats
             FROM ticket_details td
             LEFT JOIN sessions s ON s.session_id = td.session_id
             LEFT JOIN events e ON e.event_id = td.event_id
             WHERE td.ticket_details_id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $ticketDetailsId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $type = (string) ($row['ticket_type'] ?? '');
        if (in_array($type, ['day_pass', 'all_access_pass'], true)) {
            return null;
        }

        if ($row['session_id'] !== null && $row['session_id'] !== '') {
            return (int) $row['session_cap'];
        }

        if ($row['event_id'] !== null && $row['event_id'] !== '') {
            if ($row['event_seats'] === null || $row['event_seats'] === '') {
                return null;
            }

            return (int) $row['event_seats'];
        }

        return null;
    }

    /** Sum of quantities in all active carts for this ticket option. */
    public function sumActiveCartQuantityForTicketDetails(int $ticketDetailsId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(ci.quantity), 0)
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id AND c.status = 'active'
             WHERE ci.ticket_details_id = :tdid"
        );
        $stmt->execute(['tdid' => $ticketDetailsId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Seats held by pay-later orders (pending with a future expiry). Ignores legacy pending rows without expires_at.
     */
    public function sumPendingOrderQuantityForTicketDetails(int $ticketDetailsId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(oi.quantity), 0)
             FROM order_items oi
             INNER JOIN orders o ON o.order_id = oi.order_id
             WHERE o.status = 'pending'
               AND o.expires_at IS NOT NULL
               AND o.expires_at > NOW()
               AND oi.ticket_details_id = :tdid"
        );
        $stmt->execute(['tdid' => $ticketDetailsId]);

        return (int) $stmt->fetchColumn();
    }

    /** @return array{cart_item_id: int, cart_id: int, ticket_details_id: int, quantity: int}|null */
    public function findCartItemById(int $cartItemId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT cart_item_id, cart_id, ticket_details_id, quantity
             FROM cart_items
             WHERE cart_item_id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $cartItemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? [
            'cart_item_id' => (int) $row['cart_item_id'],
            'cart_id' => (int) $row['cart_id'],
            'ticket_details_id' => (int) $row['ticket_details_id'],
            'quantity' => (int) $row['quantity'],
        ] : null;
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

    public function deleteAllItemsForCart(int $cartId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM cart_items WHERE cart_id = :cid');
        $stmt->execute(['cid' => $cartId]);
    }

    public function markCartConverted(int $cartId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "UPDATE carts SET status = 'converted' WHERE cart_id = :cid"
        );
        $stmt->execute(['cid' => $cartId]);
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