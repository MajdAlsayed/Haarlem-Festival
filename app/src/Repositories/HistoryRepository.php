<?php

namespace App\Repositories;

use App\Contracts\HistoryRepositoryInterface;
use App\Core\Database;
use App\Models\HistoryTour;
use App\Models\HistoryLocation;
use App\Models\HistoryImage;

class HistoryRepository implements HistoryRepositoryInterface
{
    // TOURS
    private function mapToHistoryTours(array $row): HistoryTour
    {
        $historyTour = new HistoryTour();
        $historyTour->id = $row['history_tour_id'];
        $historyTour->sessionId = $row['session_id'];
        $historyTour->languageId = $row['language_id'];
        $historyTour->ticketsAvailable = $row['tickets_available'];

        return $historyTour;
    }

    public function getAllTours(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT ht.history_tour_id, ht.session_id, ht.language_id, ht.tickets_available, s.start_time
            FROM history_tours ht
            INNER JOIN sessions s ON ht.session_id = s.session_id 
            ORDER BY s.start_time DESC'
        );

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $historyTours = [];

        foreach ($rows as $row) {
            $historyTours[] = $this->mapToHistoryTours($row);
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
            WHERE ht.history_tour_id = :id'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryTours($row);
    }

    public function getToursByDate(string $date): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT ht.history_tour_id, ht.session_id, ht.language_id, ht.tickets_available, s.start_time
            FROM history_tours ht
            INNER JOIN sessions s ON ht.session_id = s.session_id 
            WHERE DATE(s.start_time) = :date
            ORDER BY s.start_time DESC'
        );

        $stmt->execute(['date' => $date]);
        $rows = $stmt->fetchAll();

        $historyTours = [];

        foreach ($rows as $row) {
            $historyTours[] = $this->mapToHistoryTours($row);
        }
        return $historyTours;
    }

    // LOCATIONS
    private function mapToHistoryLocations(array $row): HistoryLocation
    {
        $historyLocation = new HistoryLocation();
        $historyLocation->id = $row['history_location_id'];
        $historyLocation->name = $row['name'];
        $historyLocation->description = $row['description'];
        $historyLocation->sortOrder = $row['sort_order'];
        $historyLocation->slug = $row['slug'];
        $historyLocation->pageId = $row['page_id'];
        $historyLocation->shortDescription = $row['short_description'];

        return $historyLocation;
    }

    public function getAllLocations(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_location_id, name, slug, description, short_description, page_id, sort_order
            FROM history_locations 
            ORDER BY sort_order'
        );

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $historyLocations = [];

        foreach ($rows as $row) {
            $historyLocations[] = $this->mapToHistoryLocations($row);
        }
        return $historyLocations;
    }
    public function getLocationById(int $id): ?HistoryLocation
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_location_id, name, slug, description, short_description, page_id, sort_order
            FROM history_locations
            WHERE history_location_id = :id
            LIMIT 1'
            );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryLocations($row);
    }

    // IMAGES
    private function mapToHistoryImage(array $row): HistoryImage
    {
        $image = new HistoryImage();
        $image->id = $row['history_image_id'];
        $image->historyLocationId = $row['history_location_id'];
        $image->pageId = $row['page_id'];
        $image->eventId = $row['event_id'];
        $image->imageUrl = $row['image_url'];
        $image->altText = $row['alt_text'];
        $image->imageType = $row['image_type'];
        $image->isPrimary = (bool)$row['is_primary'];
        $image->sortOrder = $row['sort_order'];

        return $image;
    }

    public function getPrimaryImage(int $locationId): ?HistoryImage
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, page_id, event_id,
            image_url, alt_text, image_type, is_primary, sort_order
            FROM history_images
            WHERE history_location_id = :location_id
            AND image_type = :type
            LIMIT 1'
        );

        $stmt->execute(['location_id' => $locationId, 'type' => 'primary']);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImage($row);
    }

    public function getLocationImages(int $locationId): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, page_id, 
            event_id, image_url, alt_text, image_type, is_primary, sort_order
            FROM history_images
            WHERE history_location_id = :location_id
            ORDER BY sort_order'
        );

        $stmt->execute(['location_id' => $locationId]);
        $rows = $stmt->fetchAll();

        $images = [];

        foreach ($rows as $row) {
            $images[] = $this->mapToHistoryImage($row);
        }
        return $images;
    }

    public function getPageHeroImage(int $pageId): ?HistoryImage
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, page_id, event_id, 
            image_url, alt_text, image_type, is_primary, sort_order
             FROM history_images
             WHERE page_id = :page_id
             AND image_type = :type
             LIMIT 1'
        );

        $stmt->execute(['page_id' => $pageId, 'type' => 'hero']);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImage($row);
    }

    public function getEventHeroImage(int $eventId): ?HistoryImage
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
        'SELECT history_image_id, history_location_id, page_id, event_id, 
            image_url, alt_text, image_type, is_primary, sort_order
            FROM history_images
            WHERE event_id = :event_id
            AND image_type = :type
            LIMIT 1'
        );

        $stmt->execute(['event_id' => $eventId, 'type' => 'hero']);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImage($row);
    }

    public function getLocationGallery(int $locationId): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, page_id, event_id, 
            image_url, alt_text, image_type, is_primary, sort_order
            FROM history_images
            WHERE history_location_id = :location_id
            AND image_type = :type
            ORDER BY sort_order'
        );

        $stmt->execute(['location_id' => $locationId, 'type' => 'gallery']);
        $rows = $stmt->fetchAll();

        $images = [];

        foreach ($rows as $row) {
            $images[] = $this->mapToHistoryImage($row);
        }
        return $images;
    }
    public function getImageById(int $imageId): ?HistoryImage
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_image_id, history_location_id, page_id, event_id,
        image_url, alt_text, image_type, is_primary, sort_order
        FROM history_images
        WHERE history_image_id = :image_id
        LIMIT 1'
        );

        $stmt->execute(['image_id' => $imageId]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImage($row);
    }

    // PAGE BLOCKS
    public function getPageBlocks(string $slug): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT pb.block_id, pb.page_id, pb.block_type, pb.content_json, pb.sort_order
            FROM page_blocks pb
            INNER JOIN pages p ON pb.page_id = p.page_id
            WHERE p.slug = :slug
            ORDER BY pb.sort_order'
        );

        $stmt->execute(['slug' => $slug]);
        $rows = $stmt->fetchAll();

        $pageId = null;
        $blocks = [];
        foreach ($rows as $row) {
            $pageId = $row['page_id'];
            $blocks[$row['block_type']] = [
                'block_id' => $row['block_id'],
                'content' => json_decode($row['content_json'], true),
                'sort_order' => $row['sort_order']
            ];
        }
        return [
            'page_id' => $pageId,
            'blocks' => $blocks
        ];
    }
}