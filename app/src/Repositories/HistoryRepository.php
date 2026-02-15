<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\HistoryTour;


class HistoryRepository implements IHistoryRepository
{
    public function getAllTours(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT ht.history_tour_id, ht.session_id, ht.language_id, ht.tickets_available, s.start_time
            FROM history_tours ht
            INNER JOIN sessions s ON ht.session_id = s.session_id 
            ORDER BY s.start_time DESC');

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $historyTours = [];

        foreach ($rows as $row) {
            $historyTour = new HistoryTour();
            $historyTour->id = $row['history_tour_id'];
            $historyTour->sessionId = $row['session_id'];
            $historyTour->languageId = $row['language_id'];
            $historyTour->ticketsAvailable = $row['tickets_available'];

            $historyTours[] = $historyTour;
        }
        return $historyTours;
    }

    public function getTourById(int $id): ?HistoryTour
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT ht.history_tour_id, ht.session_id, ht.language_id, ht.tickets_available, s.start_time
            FROM history_tours ht
            INNER JOIN sessions s ON ht.session_id = s.session_id 
            WHERE ht.history_tour_id = :id');

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

            $historyTour = new HistoryTour();
            $historyTour->id = $row['history_tour_id'];
            $historyTour->sessionId = $row['session_id'];
            $historyTour->languageId = $row['language_id'];
            $historyTour->ticketsAvailable = $row['tickets_available'];

        return $historyTour;
    }

    public function getToursByDate(string $date): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT ht.history_tour_id, ht.session_id, ht.language_id, ht.tickets_available, s.start_time
            FROM history_tours ht
            INNER JOIN sessions s ON ht.session_id = s.session_id 
            WHERE DATE(s.start_time) = :date
            ORDER BY s.start_time DESC');

        $stmt->execute(['date' => $date]);
        $rows = $stmt->fetchall();

        $historyTours = [];

        foreach ($rows as $row) {
            $historyTour = new HistoryTour();
            $historyTour->id = $row['history_tour_id'];
            $historyTour->sessionId = $row['session_id'];
            $historyTour->languageId = $row['language_id'];
            $historyTour->ticketsAvailable = $row['tickets_available'];

            $historyTours[] = $historyTour;
        }
        return $historyTours;
    }
}