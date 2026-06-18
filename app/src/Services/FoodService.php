<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RestaurantRepositoryInterface;
use App\Contracts\ServiceInterface\FoodServiceInterface;
use App\Exceptions\NotFoundException;
use App\Models\Restaurant;
use App\Repositories\CartRepository;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\TicketDetailsRepository;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;

final class FoodService implements FoodServiceInterface
{
    private RestaurantRepositoryInterface $restaurantRepo;
    private FoodSettingsRepository        $foodSettingsRepo;
    private ReservationRepository         $reservationRepo;
    private UserRepository                $userRepo;
    private TicketDetailsRepository       $ticketDetailsRepo;
    private CartService                   $cartService;
    private ?array                        $settings = null;

    public function __construct()
    {
        $this->restaurantRepo    = new RestaurantRepository();
        $this->foodSettingsRepo  = new FoodSettingsRepository();
        $this->reservationRepo   = new ReservationRepository();
        $this->userRepo          = new UserRepository();
        $this->ticketDetailsRepo = new TicketDetailsRepository();
        $cartRepo                = new CartRepository();
        $this->cartService       = new CartService(
            $cartRepo,
            new TicketAvailabilityService($cartRepo, new TicketRepository())
        );
    }

    private function settings(): array
    {
        return $this->settings ??= $this->foodSettingsRepo->getAll();
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function getFoodIndexViewModel(): \App\ViewModels\FoodViewModel
    {
        return new \App\ViewModels\FoodViewModel(
            foodSettings: $this->settings(),
            restaurants:  $this->restaurantRepo->getAll(),
        );
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    public function getFoodSettings(): array
    {
        return $this->settings();
    }

    public function getFestivalDates(): array
    {
        $out = [];
        foreach ($this->settings()['festival_dates'] ?? [] as $entry) {
            $out[$entry['value']] = $entry['label'];
        }
        return $out;
    }

    public function getReservationFeePerPerson(): float
    {
        return (float)($this->settings()['reservation_fee_per_person'] ?? 10);
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

    public function getBookingOverviewData(int $id): array
    {
        return [
            $this->getRestaurantOrFail($id),
            $this->getFoodSettings(),
            $this->getFestivalDates(),
            $this->getReservationFeePerPerson(),
        ];
    }

    // ── Booking form ──────────────────────────────────────────────────────────

    public function getUserByEmail(string $email): ?\App\Models\User
    {
        return $this->userRepo->findByEmail($email);
    }

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
        $input = $this->sanitizeBookingInput($post);
        return ['errors' => $this->getValidationErrors($input), 'input' => $input];
    }

    // ── Reservation ───────────────────────────────────────────────────────────

    public function calculateReservationFee(array $input): float
    {
        $totalGuests = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        return $this->getReservationFeePerPerson() * $totalGuests;
    }

    public function saveBooking(int $restaurantId, ?int $userId, array $input): int
    {
        $totalGuests     = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        $reservationFee  = $this->getReservationFeePerPerson() * $totalGuests;
        $restaurant      = $this->getRestaurantOrFail($restaurantId);

        $reservationId   = $this->createReservationRecord($restaurantId, $userId, $input, $reservationFee, $totalGuests);
        $ticketDetailsId = $this->createTicketDetailsForReservation($restaurant, $input, $reservationFee, $reservationId);

        $this->cartService->addItem($ticketDetailsId, 1);

        return $reservationId;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function sanitizeBookingInput(array $post): array
    {
        return [
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
    }

    private function getValidationErrors(array $input): array
    {
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

        return $errors;
    }

    private function createReservationRecord(int $restaurantId, ?int $userId, array $input, float $fee, int $guests): int
    {
        return $this->reservationRepo->create([
            'restaurant_id'   => $restaurantId,
            'user_id'         => $userId,
            'session_time'    => $input['session_time'],
            'guests'          => $guests,
            'first_name'      => $input['first_name'],
            'last_name'       => $input['last_name'],
            'email'           => $input['email'],
            'phone'           => $input['phone'] ?: null,
            'special_request' => $input['special_request'] ?: null,
            'status'          => 'pending',
            'reservation_fee' => $fee,
        ]);
    }

    private function createTicketDetailsForReservation(
        Restaurant $restaurant,
        array      $input,
        float      $reservationFee,
        int        $reservationId
    ): int {
        $adults   = (int)($input['adults']   ?? 0);
        $children = (int)($input['children'] ?? 0);
        $date     = $input['booking_date']   ?? '';
        $time     = substr($input['session_time'], 0, 5);

        $guestParts = [];
        if ($adults > 0)   $guestParts[] = $adults   . ' adult'  . ($adults   !== 1 ? 's' : '');
        if ($children > 0) $guestParts[] = $children . ' child'  . ($children !== 1 ? 'ren' : '');

        $name        = $restaurant->name . ' — Table Reservation';
        $description = sprintf('%s · %s at %s · %s', $restaurant->name, $date, $time, implode(', ', $guestParts));

        return $this->ticketDetailsRepo->createForReservation($reservationId, $name, $description, $reservationFee);
    }
}