<?php
// app/src/Services/FoodService.php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Models\Restaurant;
use App\Repositories\CartRepository;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\TicketRepository;

final class FoodService
{
    public function __construct(
        private readonly RestaurantRepository   $restaurantRepo,
        private readonly FoodSettingsRepository $foodSettingsRepo,
        private readonly ReservationRepository  $reservationRepo,
    ) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function getFoodIndexViewModel(): \App\ViewModels\FoodViewModel
    {
        return new \App\ViewModels\FoodViewModel(
            foodSettings: $this->foodSettingsRepo->getAll(),
            restaurants:  $this->restaurantRepo->getAll(),
        );
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    public function getFoodSettings(): array
    {
        return $this->foodSettingsRepo->getAll();
    }

    public function getFestivalDates(): array
    {
        $raw = $this->foodSettingsRepo->getAll()['festival_dates'] ?? [];
        $out = [];
        foreach ($raw as $entry) {
            $out[$entry['value']] = $entry['label'];
        }
        return $out;
    }

    public function getReservationFeePerPerson(): float
    {
        return (float)($this->foodSettingsRepo->getAll()['reservation_fee_per_person'] ?? 10);
    }

    // ── Restaurant ────────────────────────────────────────────────────────────

    public function getRestaurantOrFail(int $id): Restaurant
    {
        $restaurant = $this->restaurantRepo->getById($id);
        if ($restaurant === null) {
            throw new NotFoundException('Restaurant not found');
        }
        return $restaurant;
    }

    // ── Booking form ──────────────────────────────────────────────────────────

    public function getDefaultInput(?object $user): array
    {
        return [
            'session_time'    => '',
            'booking_date'    => '',
            'adults'          => 0,
            'children'        => 0,
            'first_name'      => $user->firstName ?? '',
            'last_name'       => $user->lastName  ?? '',
            'email'           => $user->email     ?? '',
            'phone'           => '',
            'special_request' => '',
        ];
    }

    public function validateBookingInput(array $post): array
    {
        $input = [
            'session_time'    => trim((string)($post['session_time']    ?? '')),
            'booking_date'    => trim((string)($post['booking_date']    ?? '')),
            'adults'          => max(0, (int)($post['adults']           ?? 0)),
            'children'        => max(0, (int)($post['children']         ?? 0)),
            'first_name'      => trim((string)($post['first_name']      ?? '')),
            'last_name'       => trim((string)($post['last_name']       ?? '')),
            'email'           => trim((string)($post['email']           ?? '')),
            'phone'           => trim((string)($post['phone']           ?? '')),
            'special_request' => trim((string)($post['special_request'] ?? '')),
        ];

        $errors = [];

        if ($input['session_time'] === '') $errors[] = 'Session time is required.';
        if ($input['booking_date'] === '')  $errors[] = 'Date is required.';

        if (($input['adults'] + $input['children']) < 1) {
            $errors[] = 'Please add at least one adult or child.';
        }

        if ($input['first_name'] === '') $errors[] = 'First name is required.';
        if ($input['last_name']  === '') $errors[] = 'Last name is required.';

        if ($input['email'] === '' || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        return ['errors' => $errors, 'input' => $input];
    }

    // ── Reservation ───────────────────────────────────────────────────────────

    public function calculateReservationFee(array $input): float
    {
        $totalGuests = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        return $this->getReservationFeePerPerson() * $totalGuests;
    }

    public function saveBooking(int $restaurantId, ?int $userId, array $input): int
    {
        $totalGuests    = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        $reservationFee = $this->calculateReservationFee($input);
        $restaurant     = $this->getRestaurantOrFail($restaurantId);

        // 1. Save reservation
        $reservationId = $this->reservationRepo->create([
    'restaurant_id'   => $restaurantId,
    'user_id'         => $userId,
    'session_time'    => $input['session_time'],
    'guests'          => $totalGuests,
    'first_name'      => $input['first_name'],
    'last_name'       => $input['last_name'],
    'email'           => $input['email'],
    'phone'           => $input['phone'] ?: null,
    'special_request' => $input['special_request'] ?: null,
    'status'          => 'pending',
    'reservation_fee' => $reservationFee,
]);

        // 2. Create a ticket_details row for this reservation
        $ticketDetailsId = $this->createTicketDetailsForReservation(
            $restaurant,
            $input,
            $reservationFee,
            $reservationId
        );

        // 3. Add to cart
        $this->addToCart($ticketDetailsId);

        return $reservationId;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function createTicketDetailsForReservation(
    Restaurant $restaurant,
    array      $input,
    float      $reservationFee,
    int        $reservationId
): int {
    $adults   = (int) ($input['adults']   ?? 0);
    $children = (int) ($input['children'] ?? 0);
    $date     = $input['booking_date']  ?? '';
    $time     = substr($input['session_time'], 0, 5);

    $guestParts = [];
    if ($adults > 0)   $guestParts[] = $adults   . ' adult'    . ($adults   !== 1 ? 's' : '');
    if ($children > 0) $guestParts[] = $children . ' child'    . ($children !== 1 ? 'ren' : '');

    $name        = $restaurant->name . ' — Table Reservation';
    $description = sprintf(
        '%s · %s at %s · %s',
        $restaurant->name,
        $date,
        $time,
        implode(', ', $guestParts)
    );

    $db   = Database::getConnection();
    $stmt = $db->prepare('
        INSERT INTO ticket_details
            (reservation_id, event_id, session_id, ticket_type, name, description, price)
        VALUES
            (:reservation_id, NULL, NULL, :ticket_type, :name, :description, :price)
    ');

    $stmt->execute([
        'reservation_id' => $reservationId,
        'ticket_type'    => 'event_ticket',
        'name'           => $name,
        'description'    => $description,
        'price'          => number_format($reservationFee, 2, '.', ''),
    ]);

    return (int) $db->lastInsertId();
}

    private function addToCart(int $ticketDetailsId): void
    {
        $cartRepository = new CartRepository();

        $cartService = new CartService(
            $cartRepository,
            new TicketAvailabilityService(
                $cartRepository,
                new TicketRepository()
            )
        );

        $cartService->addItem($ticketDetailsId, 1);
    }
}