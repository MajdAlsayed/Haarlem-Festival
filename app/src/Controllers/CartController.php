<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Repositories\CartRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TicketRepository;
use App\Services\CartService;
use App\Services\TicketAvailabilityService;

class CartController
{
    private CartService $cartService;

    public function __construct()
    {
        $cartRepository = new CartRepository();
        $this->cartService = new CartService(
            $cartRepository,
            new TicketAvailabilityService($cartRepository, new TicketRepository())
        );
    }

    public function get(): void
    {
        $vm = $this->cartService->getCurrentCart();
        $app = (new SettingsRepository())->getAll();
        $cartCsrf = Csrf::peek('cart') ?? Csrf::token('cart');
        $success = Session::getFlash('cart_success');
        $error = Session::getFlash('cart_error');

        require __DIR__ . '/../Views/Cart/index.php';
    }

    public function apiGet(): void
    {
        $this->json([
            'success' => true,
            'cart' => $this->cartService->getCurrentCart()->toArray(),
        ]);
    }

    public function add(): void
    {
        // Normal form add uses this when JavaScript is not doing it.
        if (!$this->checkCsrf($_POST['_csrf'] ?? null)) {
            Session::setFlash('cart_error', 'Invalid form token.');
            $this->redirectBack();
        }

        try {
            $ticketDetailsId = (int)($_POST['ticket_details_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            $contributionTotal = $this->readContributionTotal($_POST['contribution_total'] ?? null);

            $this->cartService->addItem($ticketDetailsId, $quantity, $contributionTotal);
            Session::setFlash('cart_success', 'Ticket added to your cart.');
        } catch (\Exception $e) {
            Session::setFlash('cart_error', $e->getMessage());
        }

        $this->redirectBack();
    }

    public function apiAdd(): void
    {
        // JavaScript add-to-cart uses this JSON endpoint.
        $data = $this->readJsonInput();

        if (!$this->checkCsrf($data['_csrf'] ?? null)) {
            $this->jsonError('Invalid form token.', 403);
        }

        try {
            $cart = $this->cartService->addItem(
                (int)($data['ticket_details_id'] ?? 0),
                (int)($data['quantity'] ?? 1),
                $this->readContributionTotal($data['contribution_total'] ?? null)
            );

            $this->json([
                'success' => true,
                'cart' => $cart->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function apiUpdate(): void
    {
        $data = $this->readJsonInput();

        if (!$this->checkCsrf($data['_csrf'] ?? null)) {
            $this->jsonError('Invalid form token.', 403);
        }

        try {
            $cart = $this->cartService->updateItem(
                (int)($data['cart_item_id'] ?? 0),
                (int)($data['quantity'] ?? 0)
            );

            $this->json([
                'success' => true,
                'cart' => $cart->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    public function remove(): void
    {
        if (!$this->checkCsrf($_POST['_csrf'] ?? null)) {
            Session::setFlash('cart_error', 'Invalid form token.');
            header('Location: /cart');
            exit;
        }

        try {
            $this->cartService->removeItem((int)($_POST['cart_item_id'] ?? 0));
            Session::setFlash('cart_success', 'Item removed from your cart.');
        } catch (\Exception $e) {
            Session::setFlash('cart_error', $e->getMessage());
        }

        header('Location: /cart');
        exit;
    }

    public function apiRemove(): void
    {
        $data = $this->readJsonInput();

        if (!$this->checkCsrf($data['_csrf'] ?? null)) {
            $this->jsonError('Invalid form token.', 403);
        }

        try {
            $cart = $this->cartService->removeItem((int)($data['cart_item_id'] ?? 0));

            $this->json([
                'success' => true,
                'cart' => $cart->toArray(),
            ]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 400);
        }
    }

    private function checkCsrf($token): bool
    {
        // Stops another site from calling the cart API for this user.
        return Csrf::validateWithoutConsuming('cart', is_string($token) ? $token : null);
    }

    private function readContributionTotal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('Invalid contribution amount.');
        }

        return round((float)$value, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonInput(): array
    {
        // Reads the JSON data that the JavaScript sent.
        $json = file_get_contents('php://input');
        $data = json_decode($json ?: '{}', true);

        return is_array($data) ? $data : [];
    }

    private function redirectBack(): void
    {
        $return = (string)($_POST['return'] ?? '/tickets');
        if ($return === '' || $return[0] !== '/') {
            $return = '/tickets';
        }

        header('Location: ' . $return);
        exit;
    }

    private function jsonError(string $message, int $statusCode): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
