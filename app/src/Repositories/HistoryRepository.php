<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\HistoryTour;
use App\Models\HistoryLocation;
use App\Models\HistoryImage;

class HistoryRepository implements IHistoryRepository
{
    // TOURS

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

    // LOCATIONS

    public function getAllLocations(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_location_id, name, slug, description, page_id, sort_order
            FROM history_locations 
            ORDER BY sort_order');

        $stmt->execute();
        $rows = $stmt->fetchall();

        $historyLocations = [];

        foreach ($rows as $row) {
            $historyLocation = new HistoryLocation();
            $historyLocation->id = $row['history_location_id'];
            $historyLocation->name = $row['name'];
            $historyLocation->description = $row['description'];
            $historyLocation->sortOrder = $row['sort_order'];
            $historyLocation->slug = $row['slug'];
            $historyLocation->pageId = $row['page_id'];

            $historyLocations[] = $historyLocation;
        }
        return $historyLocations;
    }

    // IMAGES

    public function getPrimaryImage(int $locationId): ?HistoryImage
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, image_url, alt_text, is_primary, sort_order
             FROM history_images
             WHERE history_location_id = :location_id AND is_primary = 1
             LIMIT 1'
        );

        $stmt->execute(['location_id' => $locationId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $image = new HistoryImage();
        $image->id = $row['history_image_id'];
        $image->historyLocationId = $row['history_location_id'];
        $image->imageUrl = $row['image_url'];
        $image->altText = $row['alt_text'];
        $image->isPrimary = (bool)$row['is_primary'];
        $image->sortOrder = $row['sort_order'];

        return $image;
    }

    public function getLocationImages(int $locationId): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, image_url, alt_text, is_primary, sort_order
             FROM history_images
             WHERE history_location_id = :location_id
             ORDER BY sort_order'
        );

        $stmt->execute(['location_id' => $locationId]);
        $rows = $stmt->fetchAll();

        $images = [];

        foreach ($rows as $row) {
            $image = new HistoryImage();
            $image->id = $row['history_image_id'];
            $image->historyLocationId = $row['history_location_id'];
            $image->imageUrl = $row['image_url'];
            $image->altText = $row['alt_text'];
            $image->isPrimary = (bool)$row['is_primary'];
            $image->sortOrder = $row['sort_order'];

            $images[] = $image;
        }
        return $images;
    }
}