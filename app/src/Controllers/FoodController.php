<?php
// app/src/Controllers/FoodController.php

namespace App\Controllers;

use App\Core\Csrf;
use App\Repositories\FoodSettingsRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\ReservationRepository;
use App\Repositories\UserRepository;
use App\Services\FoodService;

class FoodController
{
    private FoodService $foodService;

    public function __construct()
    {
        $this->foodService = new FoodService(
            new RestaurantRepository(),
            new FoodSettingsRepository(),
            new ReservationRepository(),
        );
    }

    public function index(): void
    {
        $viewModel = $this->foodService->getFoodIndexViewModel();
        require __DIR__ . '/../Views/Food/Index.php';
    }

    public function restaurant(int $id): void
    {
        $restaurant   = $this->foodService->getRestaurantOrFail($id);
        $foodSettings = $this->foodService->getFoodSettings();
        require __DIR__ . '/../Views/Food/Restaurant.php';
    }

    public function booking(int $id): void
    {
        $restaurant    = $this->foodService->getRestaurantOrFail($id);
        $foodSettings  = $this->foodService->getFoodSettings();
        $festivalDates = $this->foodService->getFestivalDates();
        $errors        = [];

        $user = null;
        if (!empty($_SESSION['auth']['email'])) {
            $user = (new UserRepository())->findByEmail($_SESSION['auth']['email']);
        }
        $input = $this->foodService->getDefaultInput($user);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate('food_booking', $_POST['_csrf'] ?? null)) {
                $errors[] = 'Invalid form token. Please refresh and try again.';
            } else {
                $result = $this->foodService->validateBookingInput($_POST);
                $input  = $result['input'];
                $errors = $result['errors'];

                if (empty($errors)) {
                    $_SESSION['booking_draft'] = $input;
                    header('Location: /food/restaurant/' . $id . '/booking/overview');
                    exit;
                }
            }
        }

        require __DIR__ . '/../Views/Food/Booking.php';
    }

    public function bookingOverview(int $id): void
    {
        $restaurant    = $this->foodService->getRestaurantOrFail($id);
        $foodSettings  = $this->foodService->getFoodSettings();
        $festivalDates = $this->foodService->getFestivalDates();
        $feePerPerson  = $this->foodService->getReservationFeePerPerson();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
            if (!Csrf::validate('food_booking_confirm', $_POST['_csrf'] ?? null)) {
                header('Location: /food/restaurant/' . $id . '/booking');
                exit;
            }

            $input = $_SESSION['booking_draft'] ?? [];
            if (empty($input)) {
                header('Location: /food/restaurant/' . $id . '/booking');
                exit;
            }

            $userId         = $_SESSION['auth']['user_id'] ?? null;
            $reservationId  = $this->foodService->saveBooking($id, $userId, $input);
            $reservationFee = $this->foodService->calculateReservationFee($input);

            setcookie('user_first_name', $input['first_name'], ['expires' => time() + 60*60*24*90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            setcookie('user_last_name',  $input['last_name'],  ['expires' => time() + 60*60*24*90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            setcookie('user_email',      $input['email'],      ['expires' => time() + 60*60*24*90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);

            unset($_SESSION['booking_draft']);

            $_SESSION['open_cart_drawer'] = true;

            $successMessage = 'Reservation confirmed! Your reservation ID is #' . $reservationId;
            require __DIR__ . '/../Views/Food/BookingSuccess.php';
            exit;
        }

        if (empty($_SESSION['booking_draft'])) {
            header('Location: /food/restaurant/' . $id . '/booking');
            exit;
        }

        $input          = $_SESSION['booking_draft'];
        $totalGuests    = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        $reservationFee = $this->foodService->calculateReservationFee($input);

        require __DIR__ . '/../Views/Food/BookingOverview.php';
    }
}