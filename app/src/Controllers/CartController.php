<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\PersonalProgramRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;
use App\Services\CartService;
use App\Services\TicketAvailabilityService;

/**
 * Cart: JSON for cart drawer (fetch); HTML for /cart page; form POSTs from tickets redirect after add.
 * Mutating POSTs need CSRF (key cart); JSON sends _csrf in body; forms use hidden _csrf.
 */
final class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $this->cartService = $this->makeCartService();
    }

    private function makeCartService(): CartService
    {
        $cartRepo = new CartRepository();

        return new CartService(
            $cartRepo,
            new TicketAvailabilityService($cartRepo, new TicketRepository())
        );
    }

    public function get(): void
    {
        if ($this->wantsJsonResponse()) {
            $csrf = Csrf::peek('cart') ?? Csrf::token('cart');
            $this->json([
                'success' => true,
                'cart' => $this->cartService->getCurrentCart()->toArray(),
                'csrf' => $csrf,
            ]);
            return;
        }

        $vm = $this->cartService->getCurrentCart();
        $lines = [];
        $total = 0.0;
        foreach ($vm->items as $item) {
            $line = $item->getLineTotal();
            $total += $line;
            $lines[] = [
                'cart_item_id' => $item->cartItemId,
                'ticket_details_id' => $item->ticketDetailsId,
                'name' => $item->name,
                'qty' => $item->quantity,
                'unit' => $item->price,
                'line' => $line,
            ];
        }

        $app = (new SettingsRepository())->getAll();
        $success = Session::getFlash('cart_success');
        require __DIR__ . '/../Views/Cart/index.php';
    }

    public function add(): void
    {
        $data = $this->getInputData();
        $formPost = $this->isFormPost();

        if (!$this->requireCsrf($data, $formPost)) {
            return;
        }
        unset($data['_csrf']);

        $ticketDetailsId = (int) ($data['ticket_details_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($ticketDetailsId <= 0) {
            if ($formPost) {
                Session::setFlash('cart_error', 'Invalid ticket.');
                $this->redirectReturn($data);
                return;
            }
            $this->json([
                'success' => false,
                'message' => 'ticket_details_id is required.',
            ], 422);
            return;
        }

        try {
            $cart = $this->cartService->addItem($ticketDetailsId, $quantity);

            $loggedUserId = isset($_SESSION['auth']['user_id']) ? (int) $_SESSION['auth']['user_id'] : 0;
            if ($loggedUserId > 0) {
                (new PersonalProgramRepository())->addItem($loggedUserId, $ticketDetailsId);
            }

            if ($formPost) {
                Session::setFlash('cart_success', 'Added to your cart.');
                $this->redirectReturn($data);
                return;
            }

            $this->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'cart' => $cart->toArray(),
                'csrf' => Csrf::token('cart'),
            ]);
        } catch (\Throwable $e) {
            if ($formPost) {
                Session::setFlash('cart_error', $e->getMessage());
                $this->redirectReturn($data);
                return;
            }
            $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(): void
    {
        $data = $this->getInputData();
        $formPost = $this->isFormPost();

        if (!$this->requireCsrf($data, $formPost)) {
            return;
        }
        unset($data['_csrf']);

        $cartItemId = (int) ($data['cart_item_id'] ?? 0);
        $quantity = (int) ($data['quantity'] ?? 0);

        if ($cartItemId <= 0) {
            if ($formPost) {
                header('Location: /cart');
                exit;
            }
            $this->json([
                'success' => false,
                'message' => 'cart_item_id is required.',
            ], 422);
            return;
        }

        $cart = $this->cartService->updateItem($cartItemId, $quantity);

        if ($formPost) {
            header('Location: /cart');
            exit;
        }

        $this->json([
            'success' => true,
            'message' => 'Cart updated.',
            'cart' => $cart->toArray(),
            'csrf' => Csrf::token('cart'),
        ]);
    }

    public function remove(): void
    {
        $data = $this->getInputData();
        $formPost = $this->isFormPost();

        if (!$this->requireCsrf($data, $formPost)) {
            return;
        }
        unset($data['_csrf']);

        $cartItemId = (int) ($data['cart_item_id'] ?? 0);

        if ($cartItemId <= 0) {
            if ($formPost) {
                header('Location: /cart');
                exit;
            }
            $this->json([
                'success' => false,
                'message' => 'cart_item_id is required.',
            ], 422);
            return;
        }

        $cart = $this->cartService->removeItem($cartItemId);

        if ($formPost) {
            header('Location: /cart');
            exit;
        }

        $this->json([
            'success' => true,
            'message' => 'Item removed.',
            'cart' => $cart->toArray(),
            'csrf' => Csrf::token('cart'),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function requireCsrf(array $data, bool $formPost): bool
    {
        $token = isset($data['_csrf']) && is_string($data['_csrf']) ? $data['_csrf'] : null;
        if (Csrf::validate('cart', $token)) {
            return true;
        }

        if ($formPost) {
            Session::setFlash('cart_error', 'Invalid or expired security token. Please try again.');
            $this->redirectReturn($data);
            return false;
        }

        $this->json([
            'success' => false,
            'message' => 'Invalid or expired security token.',
            'csrf' => Csrf::token('cart'),
        ], 403);

        return false;
    }

    /** @param array<string, mixed> $data */
    private function redirectReturn(array $data): void
    {
        $return = trim((string) ($data['return'] ?? '/tickets'));
        if ($return === '' || !str_starts_with($return, '/')) {
            $return = '/tickets';
        }
        header('Location: ' . $return);
        exit;
    }

    private function isFormPost(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($contentType, 'application/x-www-form-urlencoded')
            || str_contains($contentType, 'multipart/form-data');
    }

    private function getInputData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw ?: '{}', true);

            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    private function wantsJsonResponse(): bool
    {
        $dest = $_SERVER['HTTP_SEC_FETCH_DEST'] ?? '';
        if ($dest === 'document') {
            return false;
        }
        if ($dest !== '') {
            return true;
        }
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'text/html') && !str_contains($accept, 'application/json')) {
            return false;
        }

        return true;
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
