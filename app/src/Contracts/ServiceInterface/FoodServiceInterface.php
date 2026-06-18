<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

use App\Models\Restaurant;
use App\Models\User;
use App\ViewModels\FoodViewModel;

interface FoodServiceInterface
{
    public function getFoodIndexViewModel(): FoodViewModel;

    public function getFoodSettings(): array;

    public function getFestivalDates(): array;

    public function getReservationFeePerPerson(): float;

    public function getRestaurantOrFail(int $id): Restaurant;

    /** @return array{0: Restaurant, 1: array, 2: array, 3: float} */
    public function getBookingOverviewData(int $id): array;

    public function getUserByEmail(string $email): ?User;

    /** @param object|null $user */
    public function getDefaultInput(?object $user): array;

    /** @param array<string, mixed> $post */
    public function validateBookingInput(array $post): array;

    /** @param array<string, mixed> $input */
    public function calculateReservationFee(array $input): float;

    /** @param array<string, mixed> $input */
    public function saveBooking(int $restaurantId, ?int $userId, array $input): int;
}