<?php

namespace App\Controllers;

use App\Contracts\ServiceInterface\FoodServiceInterface;
use App\Core\Csrf;
use App\Core\Session;
use App\Services\FoodService;

class FoodController
{
    private FoodServiceInterface $foodService;

    public function __construct()
    {
        $this->foodService = new FoodService();
    }

    public function index(): void
    {
        try {
            $viewModel = $this->foodService->getFoodIndexViewModel();
        } catch (\Throwable $e) {
            error_log('[FoodController::index] ' . $e->getMessage());
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
            error_log('[FoodController::restaurant] ' . $e->getMessage());
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
            error_log('[FoodController::booking] ' . $e->getMessage());
            header('Location: /food');
            exit;
        }

        $user = null;
        if (!empty($_SESSION['auth']['email'])) {
            try {
                $user = $this->foodService->getUserByEmail($_SESSION['auth']['email']);
            } catch (\Throwable) {}
        }

        $input  = $this->foodService->getDefaultInput($user);
        $errors = [];

        require __DIR__ . '/../Views/Food/Booking.php';
    }

    public function submitBooking(int $id): void
    {
        try {
            $restaurant    = $this->foodService->getRestaurantOrFail($id);
            $foodSettings  = $this->foodService->getFoodSettings();
            $festivalDates = $this->foodService->getFestivalDates();
        } catch (\Throwable $e) {
            error_log('[FoodController::submitBooking] ' . $e->getMessage());
            header('Location: /food');
            exit;
        }

        if (!Csrf::validate('food_booking', $_POST['_csrf'] ?? null)) {
            header('Location: /food/restaurant/' . $id . '/booking');
            exit;
        }

        $result = $this->foodService->validateBookingInput($_POST);
        $input  = $result['input'];
        $errors = $result['errors'];

        if (empty($errors)) {
            $_SESSION['booking_draft'] = $input;
            header('Location: /food/restaurant/' . $id . '/booking/overview');
            exit;
        }

        require __DIR__ . '/../Views/Food/Booking.php';
    }

    public function bookingOverview(int $id): void
    {
        try {
            [$restaurant, $foodSettings, $festivalDates, $feePerPerson] = $this->foodService->getBookingOverviewData($id);
        } catch (\Throwable $e) {
            error_log('[FoodController::bookingOverview] ' . $e->getMessage());
            header('Location: /food');
            exit;
        }

        if (empty($_SESSION['booking_draft'])) {
            header('Location: /food/restaurant/' . $id . '/booking');
            exit;
        }

        $input          = $_SESSION['booking_draft'];
        $totalGuests    = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        $reservationFee = $feePerPerson * $totalGuests;

        require __DIR__ . '/../Views/Food/BookingOverview.php';
    }

    public function confirmBooking(int $id): void
    {
        try {
            [$restaurant, $foodSettings, $festivalDates, $feePerPerson] = $this->foodService->getBookingOverviewData($id);
        } catch (\Throwable $e) {
            error_log('[FoodController::confirmBooking] ' . $e->getMessage());
            header('Location: /food');
            exit;
        }

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
            $userId        = $_SESSION['auth']['user_id'] ?? null;
            $reservationId = $this->foodService->saveBooking($id, $userId, $input);
        } catch (\Throwable $e) {
            error_log('[FoodController::confirmBooking] ' . $e->getMessage());
            Session::setFlash('food_booking_error', 'Could not save your reservation. Please try again.');
            header('Location: /food/restaurant/' . $id . '/booking');
            exit;
        }

        $totalGuests    = ($input['adults'] ?? 0) + ($input['children'] ?? 0);
        $reservationFee = $feePerPerson * $totalGuests;

        setcookie('user_first_name', $input['first_name'], ['expires' => time() + 60*60*24*90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        setcookie('user_last_name',  $input['last_name'],  ['expires' => time() + 60*60*24*90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        setcookie('user_email',      $input['email'],      ['expires' => time() + 60*60*24*90, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);

        unset($_SESSION['booking_draft']);
        $_SESSION['open_cart_drawer'] = true;

        $successMessage = 'Reservation confirmed! Your reservation ID is #' . $reservationId;
        require __DIR__ . '/../Views/Food/BookingSuccess.php';
    }
}