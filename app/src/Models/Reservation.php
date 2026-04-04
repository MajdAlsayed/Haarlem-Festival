<?php

declare(strict_types=1);

namespace App\Models;

class Reservation
{
    public int     $reservationId;
    public int     $restaurantId;
    public ?int    $userId;
    public string  $sessionTime;
    public int     $guests;
    public string  $firstName;
    public string  $lastName;
    public string  $email;
    public ?string $phone;
    public ?string $specialRequest;
    public string  $status;
    public float   $reservationFee;
    public string  $createdAt;
}