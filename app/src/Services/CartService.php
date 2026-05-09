<?php

namespace App\Services;

use App\Repositories\CartRepository;
use App\ViewModels\CartViewModel;

/**
 * The brain between HTTP and the database: figures out which cart id applies to this request,
 * then adds/updates/removes lines while TicketAvailabilityService enforces seat limits.
 */
class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private TicketAvailabilityService $availability
    ) {
    }

    /** Current basket for the session/user, or an empty view-model if nothing is open yet. */
    public function getCurrentCart(): CartViewModel
    {
        $cartId = $this->resolveCurrentCartId(createIfMissing: false);

        if ($cartId === null) {
            return new CartViewModel(null, []);
        }

        $items = $this->cartRepository->getCartItemsDetailed($cartId);

        return new CartViewModel($cartId, $items);
    }

    /** Adds or merges a line after checking the catalog id exists and there is enough capacity left. */
    public function addItem(int $ticketDetailsId, int $quantity = 1): CartViewModel
    {
        if ($quantity < 1) {
            $quantity = 1;
        }

        if (!$this->cartRepository->ticketDetailsExists($ticketDetailsId)) {
            throw new \InvalidArgumentException('Invalid ticket_details_id.');
        }

        $this->availability->assertDeltaAllowed($ticketDetailsId, $quantity);

        $cartId = $this->resolveCurrentCartId(createIfMissing: true);

        $existingItem = $this->cartRepository->findCartItem($cartId, $ticketDetailsId);

        if ($existingItem) {
            $this->cartRepository->incrementCartItem((int)$existingItem['cart_item_id'], $quantity);
        } else {
            $this->cartRepository->addCartItem($cartId, $ticketDetailsId, $quantity);
        }

        return $this->getCurrentCart();
    }

    /** Sets a new quantity, or deletes the line when quantity is 0 (after releasing capacity). */
    public function updateItem(int $cartItemId, int $quantity): CartViewModel
    {
        $existing = $this->cartRepository->findCartItemById($cartItemId);
        if ($existing === null) {
            return $this->getCurrentCart();
        }

        if ($quantity <= 0) {
            $this->availability->assertDeltaAllowed($existing['ticket_details_id'], -$existing['quantity']);
            $this->cartRepository->deleteCartItem($cartItemId);
            return $this->getCurrentCart();
        }

        $delta = $quantity - $existing['quantity'];
        $this->availability->assertDeltaAllowed($existing['ticket_details_id'], $delta);
        $this->cartRepository->updateCartItemQuantity($cartItemId, $quantity);

        return $this->getCurrentCart();
    }

    /** Hard delete one line — treats it like lowering qty to zero for availability. */
    public function removeItem(int $cartItemId): CartViewModel
    {
        $existing = $this->cartRepository->findCartItemById($cartItemId);
        if ($existing !== null) {
            $this->availability->assertDeltaAllowed($existing['ticket_details_id'], -$existing['quantity']);
        }
        $this->cartRepository->deleteCartItem($cartItemId);

        return $this->getCurrentCart();
    }

    /**
     * Picks the cart id: logged-in users reuse their row; guests use `$_SESSION['cart_id']`;
     * logging in may merge a guest cart into the account.
     */
    private function resolveCurrentCartId(bool $createIfMissing): ?int
    {
        $userId = isset($_SESSION['auth']['user_id']) ? (int)$_SESSION['auth']['user_id'] : null;

        if ($userId !== null) {
            $userCart = $this->cartRepository->findActiveCartByUserId($userId);
            if ($userCart) {
                return $userCart->cartId;
            }

            // Logged in after browsing as guest: merge session cart into the user row so items are not lost.
            $sessionCartId = isset($_SESSION['cart_id']) ? (int)$_SESSION['cart_id'] : null;
            if ($sessionCartId) {
                $sessionCart = $this->cartRepository->findActiveCartById($sessionCartId);
                if ($sessionCart) {
                    $this->cartRepository->attachCartToUser($sessionCart->cartId, $userId);
                    unset($_SESSION['cart_id']);
                    return $sessionCart->cartId;
                }
            }

            if (!$createIfMissing) {
                return null;
            }

            return $this->cartRepository->createCart($userId);
        }

        // Guest: cart id lives in session until login attaches it to a user.
        $sessionCartId = isset($_SESSION['cart_id']) ? (int)$_SESSION['cart_id'] : null;
        if ($sessionCartId) {
            $sessionCart = $this->cartRepository->findActiveCartById($sessionCartId);
            if ($sessionCart) {
                return $sessionCart->cartId;
            }
        }

        if (!$createIfMissing) {
            return null;
        }

        $cartId = $this->cartRepository->createCart(null);
        $_SESSION['cart_id'] = $cartId;

        return $cartId;
    }
}