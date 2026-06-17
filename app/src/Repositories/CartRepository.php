<?php

namespace App\Repositories;

use App\Core\Repository;
use App\Models\Cart;
use App\Models\CartItem;
use PDO;

/**
 * Everything that touches `carts` and `cart_items` plus helpers the availability service needs (capacity, reserved counts).
 * Higher-level rules (who owns the cart, merge on login) live in CartService.
 */
class CartRepository extends Repository
{
    /** Latest open cart for this login, if any. */
    public function findActiveCartByUserId(int $userId): ?Cart
    {
        $stmt = $this->db->prepare(
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

    /** Guest carts use this after we stash `cart_id` in the session. */
    public function findActiveCartById(int $cartId): ?Cart
    {
        $stmt = $this->db->prepare(
            "SELECT cart_id, user_id, status, created_at
             FROM carts
             WHERE cart_id = :cart_id AND status = 'active'
             LIMIT 1"
        );

        $stmt->execute(['cart_id' => $cartId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToCart($row) : null;
    }

    /** New empty cart — `user_id` null means “browser session cart”. */
    public function createCart(?int $userId): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO carts (user_id, status, created_at)
             VALUES (:user_id, 'active', NOW())"
        );

        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    /** Called when someone logs in with items still in a guest cart — points the row at their user id. */
    public function attachCartToUser(int $cartId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE carts
             SET user_id = :user_id
             WHERE cart_id = :cart_id"
        );

        $stmt->execute([
            'user_id' => $userId,
            'cart_id' => $cartId,
        ]);
    }

    /** One line in the basket for this catalog id, if it already exists (used to merge quantities). */
    public function findCartItem(int $cartId, int $ticketDetailsId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT cart_item_id, cart_id, ticket_details_id, quantity, contribution_total
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

    /** Inserts a brand-new cart line (first time this ticket type is added). */
    public function addCartItem(int $cartId, int $ticketDetailsId, int $quantity, ?float $contributionTotal = null): int
    {
        // contribution_total stores a chosen amount instead of a fixed price.
        $stmt = $this->db->prepare(
            "INSERT INTO cart_items (cart_id, ticket_details_id, quantity, contribution_total)
             VALUES (:cart_id, :ticket_details_id, :quantity, :contribution_total)"
        );

        $stmt->bindValue(':cart_id', $cartId, PDO::PARAM_INT);
        $stmt->bindValue(':ticket_details_id', $ticketDetailsId, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        if ($contributionTotal === null) {
            $stmt->bindValue(':contribution_total', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':contribution_total', number_format($contributionTotal, 2, '.', ''));
        }
        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    /** Adds more seats onto an existing line (`quantity + :delta`). */
    public function incrementCartItem(int $cartItemId, int $quantity, ?float $contributionTotal = null): void
    {
        // Pay-as-you-like adds the new chosen amount to the existing line.
        $stmt = $this->db->prepare(
            "UPDATE cart_items
             SET quantity = quantity + :quantity,
                 contribution_total = CASE
                    WHEN :contribution_total_check IS NULL THEN contribution_total
                    ELSE COALESCE(contribution_total, 0) + :contribution_total
                 END
             WHERE cart_item_id = :cart_item_id"
        );

        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        if ($contributionTotal === null) {
            $stmt->bindValue(':contribution_total_check', null, PDO::PARAM_NULL);
            $stmt->bindValue(':contribution_total', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':contribution_total_check', number_format($contributionTotal, 2, '.', ''));
            $stmt->bindValue(':contribution_total', number_format($contributionTotal, 2, '.', ''));
        }
        $stmt->bindValue(':cart_item_id', $cartItemId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /** Sets absolute quantity (used when the user types a number on /cart). */
    public function updateCartItemQuantity(int $cartItemId, int $quantity): void
    {
        $stmt = $this->db->prepare(
            "UPDATE cart_items
             SET quantity = :quantity
             WHERE cart_item_id = :cart_item_id"
        );

        $stmt->execute([
            'quantity' => $quantity,
            'cart_item_id' => $cartItemId,
        ]);
    }

    /** Removes a single line (or whole line when qty hits zero in the service). */
    public function deleteCartItem(int $cartItemId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM cart_items
             WHERE cart_item_id = :cart_item_id"
        );

        $stmt->execute(['cart_item_id' => $cartItemId]);
    }

    /** Quick guard before we insert — stops typos and deleted catalog ids. */
    public function ticketDetailsExists(int $ticketDetailsId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT ticket_details_id
             FROM ticket_details
             WHERE ticket_details_id = :ticket_details_id
             LIMIT 1"
        );

        $stmt->execute(['ticket_details_id' => $ticketDetailsId]);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * How many seats exist for this catalog row: tied to a session cap, an event’s `seats`, or unlimited (passes / no cap).
     */
    public function getTicketDetailsCapacity(int $ticketDetailsId): ?int
    {
        if ($ticketDetailsId <= 0) {
            return null;
        }
        $row = $this->fetchTicketDetailsCapacityRow($ticketDetailsId);
        if (!$row) {
            return null;
        }

        return $this->capacityFromJoinedTicketRow($row);
    }

    /**
     * Same as {@see getTicketDetailsCapacity} but in one query for many ids — used by the admin catalog table.
     *
     * @param list<int> $ticketDetailsIds
     * @return array<int, ?int> ticket_details_id => capacity or null
     */
    public function getTicketDetailsCapacitiesForIds(array $ticketDetailsIds): array
    {
        $ids = [];
        foreach ($ticketDetailsIds as $tid) {
            $i = (int) $tid;
            if ($i > 0) {
                $ids[] = $i;
            }
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare(
            "SELECT td.ticket_details_id, td.ticket_type, td.session_id, td.event_id,
                    s.tickets_available AS session_cap,
                    e.seats AS event_seats
             FROM ticket_details td
             LEFT JOIN sessions s ON s.session_id = td.session_id
             LEFT JOIN events e ON e.event_id = td.event_id
             WHERE td.ticket_details_id IN ($placeholders)"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row['ticket_details_id']] = $this->capacityFromJoinedTicketRow($row);
        }

        return $out;
    }

    /**
     * Single-row join used by {@see getTicketDetailsCapacity}.
     *
     * @return ?array<string,mixed>
     */
    private function fetchTicketDetailsCapacityRow(int $ticketDetailsId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT td.ticket_details_id, td.ticket_type, td.session_id, td.event_id,
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

        return $row ?: null;
    }

    /**
     * Turns the joined DB row into a single seat limit (or null = no numeric cap for availability math).
     *
     * @param array<string,mixed> $row joined ticket_details + sessions + events
     */
    private function capacityFromJoinedTicketRow(array $row): ?int
    {
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

    /** How many tickets of this type are currently “held” in open carts — counts toward the cap. */
    public function sumActiveCartQuantityForTicketDetails(int $ticketDetailsId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(ci.quantity), 0)
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id AND c.status = 'active'
             WHERE ci.ticket_details_id = :tdid"
        );
        $stmt->execute(['tdid' => $ticketDetailsId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Pay-later orders still reserve seats until they expire or get paid — this counts those pending lines.
     * Rows without `expires_at` are ignored so stray seed data cannot block sales forever.
     */
    public function sumPendingOrderQuantityForTicketDetails(int $ticketDetailsId): int
    {
        $stmt = $this->db->prepare(
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

    /**
     * Looks up one basket line by primary key (update/remove paths).
     *
     * @return array{cart_item_id: int, cart_id: int, ticket_details_id: int, quantity: int, contribution_total: ?float}|null
     */
    public function findCartItemById(int $cartItemId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT cart_item_id, cart_id, ticket_details_id, quantity, contribution_total
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
            'contribution_total' => $row['contribution_total'] !== null ? (float) $row['contribution_total'] : null,
        ] : null;
    }

    /**
     * Lines for the drawer and /cart — includes ticket name, price, and event title/time for display.
     *
     * @return CartItem[]
     */
    public function getCartItemsDetailed(int $cartId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ci.cart_item_id,
                ci.cart_id,
                ci.ticket_details_id,
                ci.quantity,
                ci.contribution_total,
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

    /** Clears every line — used after checkout converts the cart. */
    public function deleteAllItemsForCart(int $cartId): void
    {
        $stmt = $this->db->prepare('DELETE FROM cart_items WHERE cart_id = :cid');
        $stmt->execute(['cid' => $cartId]);
    }

    /** Stops the old cart from counting toward “reserved” stock once an order is placed. */
    public function markCartConverted(int $cartId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE carts SET status = 'converted' WHERE cart_id = :cid"
        );
        $stmt->execute(['cid' => $cartId]);
    }

    /** PDO row → Cart model. */
    private function mapRowToCart(array $row): Cart
    {
        $cart = new Cart();
        $cart->cartId = (int)$row['cart_id'];
        $cart->userId = isset($row['user_id']) ? (int)$row['user_id'] : null;
        $cart->status = (string)$row['status'];
        $cart->createdAt = isset($row['created_at']) ? (string)$row['created_at'] : null;

        return $cart;
    }

    /** PDO row → CartItem model (includes joined event fields for the UI). */
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
        $item->contributionTotal = $row['contribution_total'] !== null ? (float)$row['contribution_total'] : null;
        if ($item->contributionTotal !== null && $item->quantity > 0) {
            $item->price = $item->contributionTotal / $item->quantity;
        } else {
            $item->price = (float)$row['price'];
        }
        $item->eventId = isset($row['event_id']) ? (int)$row['event_id'] : null;
        $item->eventTitle = $row['event_title'] ?? null;
        $item->eventDay = $row['event_day'] ?? null;
        $item->startTime = $row['start_time'] ?? null;

        return $item;
    }
}
