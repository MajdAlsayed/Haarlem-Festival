<?php
// app/src/Controllers/FoodController.php

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Session;
use App\Services\FoodService;

class FoodController
{
    private FoodService $foodService;

    public function __construct()
    {
        $this->foodService = new FoodService();
    }

    public function index(): void
    {
        try {
            $viewModel = $this->foodService->getFoodIndexViewModel();
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Unable to load the food page. Please try again later.');
            header('Location: /');
            exit;
        }
        require __DIR__ . '/../Views/Food/Index.php';
    }

    public function restaurant(int $id): void
    {
        try {
            $restaurant   = $this->foodService->getRestaurantOrFail($id);
            $foodSettings = $this->foodService->getFoodSettings();
        } catch (\Throwable $e) {
            header('Location: /food');
            exit;
        }
        require __DIR__ . '/../Views/Food/Restaurant.php';
    }

    public function booking(int $id): void
    {
        try {
            $restaurant    = $this->foodService->getRestaurantOrFail($id);
            $foodSettings  = $this->foodService->getFoodSettings();
            $festivalDates = $this->foodService->getFestivalDates();
        } catch (\Throwable $e) {
            header('Location: /food');
            exit;
        }

        $errors = [];

        $user = null;
        if (!empty($_SESSION['auth']['email'])) {
            try {
                $user = $this->foodService->getUserByEmail($_SESSION['auth']['email']);
            } catch (\Throwable $e) {
                $user = null;
            }
        }
        $input = $this->foodService->getDefaultInput($user);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Csrf::validate('food_booking', $_POST['_csrf'] ?? null)) {
                $errors[] = 'Invalid form token. Please refresh and try again.';
            } else {
                try {
                    $result = $this->foodService->validateBookingInput($_POST);
                    $input  = $result['input'];
                    $errors = $result['errors'];

                    if (empty($errors)) {
                        $_SESSION['booking_draft'] = $input;
                        header('Location: /food/restaurant/' . $id . '/booking/overview');
                        exit;
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'An unexpected error occurred. Please try again.';
                }
            }
        }

        require __DIR__ . '/../Views/Food/Booking.php';
    }

    public function bookingOverview(int $id): void
    {
        try {
            $restaurant    = $this->foodService->getRestaurantOrFail($id);
            $foodSettings  = $this->foodService->getFoodSettings();
            $festivalDates = $this->foodService->getFestivalDates();
            $feePerPerson  = $this->foodService->getReservationFeePerPerson();
        } catch (\Throwable $e) {
            header('Location: /food');
            exit;
        }

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

            try {
                $userId         = $_SESSION['auth']['user_id'] ?? null;
                $reservationId  = $this->foodService->saveBooking($id, $userId, $input);
                $reservationFee = $this->foodService->calculateReservationFee($input);
            } catch (\Throwable $e) {
                Session::setFlash('food_booking_error', 'Could not save your reservation. Please try again.');
                header('Location: /food/restaurant/' . $id . '/booking');
                exit;
            }

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
        try {
            $reservationFee = $this->foodService->calculateReservationFee($input);
        } catch (\Throwable $e) {
            $reservationFee = 0;
        }

        require __DIR__ . '/../Views/Food/BookingOverview.php';
    }
}