<?php

namespace App\Services;

use App\Repositories\CartRepository;
use App\ViewModels\CartViewModel;

class CartService
{
    private CartRepository $cartRepository;
    private TicketAvailabilityService $availability;

    public function __construct(CartRepository $cartRepository, TicketAvailabilityService $availability)
    {
        $this->cartRepository = $cartRepository;
        $this->availability = $availability;
    }

    public function getCurrentCart(): CartViewModel
    {
        $cartId = $this->resolveCurrentCartId(false); // false = do not create a new cart.

        if ($cartId === null) {
            return new CartViewModel(null, []);
        }

        $items = $this->cartRepository->getCartItemsDetailed($cartId);

        return new CartViewModel($cartId, $items);
    }

    public function addItem(int $ticketDetailsId, int $quantity = 1, ?float $contributionTotal = null): CartViewModel
    {
        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($contributionTotal !== null && $contributionTotal < 0) {
            throw new \InvalidArgumentException('Contribution cannot be negative.');
        }

        if (!$this->cartRepository->ticketDetailsExists($ticketDetailsId)) {
            throw new \InvalidArgumentException('Invalid ticket.');
        }

        $this->availability->assertDeltaAllowed($ticketDetailsId, $quantity);

        $cartId = $this->resolveCurrentCartId(true); // true = create a cart if missing.

        $existingItem = $this->cartRepository->findCartItem($cartId, $ticketDetailsId);

        if ($existingItem) {
            $this->cartRepository->incrementCartItem((int)$existingItem['cart_item_id'], $quantity, $contributionTotal);
        } else {
            $this->cartRepository->addCartItem($cartId, $ticketDetailsId, $quantity, $contributionTotal);
        }

        return $this->getCurrentCart();
    }

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

    public function removeItem(int $cartItemId): CartViewModel
    {
        $existing = $this->cartRepository->findCartItemById($cartItemId);
        if ($existing !== null) {
            $this->availability->assertDeltaAllowed($existing['ticket_details_id'], -$existing['quantity']);
        }
        $this->cartRepository->deleteCartItem($cartItemId);

        return $this->getCurrentCart();
    }

    private function resolveCurrentCartId(bool $createIfMissing): ?int
    {
        // NOTE: not in course slides (sessions keep the visitor's cart between requests).
        $userId = isset($_SESSION['auth']['user_id']) ? (int)$_SESSION['auth']['user_id'] : null;

        if ($userId !== null) {
            $userCart = $this->cartRepository->findActiveCartByUserId($userId);
            if ($userCart) {
                return $userCart->cartId;
            }

            // After login, keep the guest cart by attaching it to the user.
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

        // This is the guest cart before the user logs in.
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
