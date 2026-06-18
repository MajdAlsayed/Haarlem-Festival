<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

use App\Models\Restaurant;

interface AdminFoodServiceInterface
{
    /** @return Restaurant[] */
    public function getAllRestaurants(): array;

    public function getRestaurantById(int $id): ?Restaurant;

    /** @param array<string, mixed> $post
     *  @return list<string> */
    public function validateRestaurantPost(array $post): array;

    /** @param array<string, mixed> $post */
    public function buildRestaurantFromPost(array $post): Restaurant;

    /** @param array<string, mixed> $post */
    public function createRestaurant(array $post): int;

    /** @param array<string, mixed> $post */
    public function updateRestaurant(int $id, array $post): void;

    public function deleteRestaurant(int $id): void;

    /** @return array<string, mixed> */
    public function getAllFoodSettings(): array;

    /** @param array<string, mixed> $post
     *  @return list<string> */
    public function saveSettings(array $post): array;
}