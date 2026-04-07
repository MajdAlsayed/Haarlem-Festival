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
 * Shopping cart: the header drawer and `/cart` both hit this controller.
 *
 * Browser fetches JSON to refresh counts without reloading; traditional forms from `/tickets` or jazz pages POST here
 * and then redirect back with a flash. Anything that changes the cart must send a valid CSRF token (`cart`).
 */
final class CartController
{
    private CartService $cartService;

    /** Builds CartService with the real repos and the same availability rules as checkout. */
    public function __construct()
    {
        $this->cartService = $this->makeCartService();
    }

    /** Small factory so we don’t duplicate `new TicketAvailabilityService(...)` all over the class. */
    private function makeCartService(): CartService
    {
        $cartRepo = new CartRepository();

        return new CartService(
            $cartRepo,
            new TicketAvailabilityService($cartRepo, new TicketRepository())
        );
    }

    /**
     * GET `/cart` or `/cart/json`: returns either the full HTML cart page or JSON + fresh CSRF for the drawer script.
     */
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

    /**
     * Add a line (JSON or form). On success, logged-in users also get the ticket mirrored into their saved personal program.
     */
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
                // Personal program: mirror cart adds into the saved picks list for logged-in users.
                (new PersonalProgramRepository())->addItem($loggedUserId, $ticketDetailsId);
            }

            if ($formPost) {
                Session::setFlash('cart_success', 'Ticket added to your cart.');
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

    /** Change quantity for one cart line (0 removes — handled inside CartService). */
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

    /** Drop one line entirely; capacity is “given back” before the row is deleted. */
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

    /**
     * Shared CSRF check for JSON vs form: on failure, either flash+redirect or JSON 403 with a new token.
     *
     * @param array<string, mixed> $data
     */
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

    /**
     * After a form POST, send the user back to `return` (must be a same-site path starting with `/`).
     *
     * @param array<string, mixed> $data
     */
    private function redirectReturn(array $data): void
    {
        $return = trim((string) ($data['return'] ?? '/tickets'));
        if ($return === '' || !str_starts_with($return, '/')) {
            $return = '/tickets';
        }
        header('Location: ' . $return);
        exit;
    }

    /** True when the client sent a classic HTML form (not JSON). */
    private function isFormPost(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($contentType, 'application/x-www-form-urlencoded')
            || str_contains($contentType, 'multipart/form-data');
    }

    /** Reads JSON body or `$_POST` depending on Content-Type so one action supports fetch and forms. */
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

    /**
     * Heuristic: full page navigation usually wants HTML; fetch/XHR wants JSON. Used by `get()` for the hybrid endpoint.
     */
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

    /** Sends JSON and stops — shared by every cart API branch. */
    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
