<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ReservationRepository
{
    public function create(array $data): int
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'INSERT INTO reservations (
                restaurant_id,
                user_id,
                session_time,
                guests,
                first_name,
                last_name,
                email,
                phone,
                special_request,
                status,
                reservation_fee
            ) VALUES (
                :restaurant_id,
                :user_id,
                :session_time,
                :guests,
                :first_name,
                :last_name,
                :email,
                :phone,
                :special_request,
                :status,
                :reservation_fee
            )'
        );

        $stmt->bindValue(':restaurant_id', $data['restaurant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $data['user_id'] ?? null, is_null($data['user_id']) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':session_time', $data['session_time'], PDO::PARAM_STR);
        $stmt->bindValue(':guests', $data['guests'], PDO::PARAM_INT);
        $stmt->bindValue(':first_name', $data['first_name'], PDO::PARAM_STR);
        $stmt->bindValue(':last_name', $data['last_name'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'] ?? null, $data['phone'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':special_request', $data['special_request'] ?? null, $data['special_request'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'] ?? 'pending', PDO::PARAM_STR);
        $stmt->bindValue(':reservation_fee', $data['reservation_fee'], PDO::PARAM_STR);

        $stmt->execute();

        return (int)$db->lastInsertId();
    }
}
