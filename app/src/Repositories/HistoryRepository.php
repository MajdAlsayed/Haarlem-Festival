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
    private function mapToHistoryTours(object $row): HistoryTour
    {
        $historyTour = new HistoryTour();
        $historyTour->id = (int)$row->history_tour_id;
        $historyTour->sessionId = (int)$row->session_id;
        $historyTour->languageId = (int)$row->language_id;
        $historyTour->ticketsAvailable = $row->tickets_available;

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
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        return array_map([$this, 'mapToHistoryTours'], $rows);
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
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

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
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        return array_map([$this, 'mapToHistoryTours'], $rows);
    }

    // LOCATIONS
    private function mapToHistoryLocations(object $row): HistoryLocation
    {
        $historyLocation = new HistoryLocation();
        $historyLocation->id = (int)$row->history_location_id;
        $historyLocation->name = $row->name;
        $historyLocation->description1 = $row->description_1;
        $historyLocation->description2 = $row->description_2;
        $historyLocation->sortOrder = $row->sort_order;
        $historyLocation->slug = $row->slug;
        $historyLocation->pageId = (int)$row->page_id;
        $historyLocation->shortDescription = $row->short_description;
        $historyLocation->pageSlug = $row->page_slug ?? null;

        return $historyLocation;
    }

    public function getAllLocations(): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_location_id, name, slug, description_1, description_2, short_description, page_id, sort_order
            FROM history_locations 
            ORDER BY sort_order'
        );

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        return array_map([$this, 'mapToHistoryLocations'], $rows);
    }

    public function getLocationById(int $id): ?HistoryLocation
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_location_id, name, slug, description_1, description_2, short_description, page_id, sort_order
            FROM history_locations
            WHERE history_location_id = :id
            LIMIT 1'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryLocations($row);
    }

    public function getLocationBySlug(string $slug): ?HistoryLocation
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT hl.history_location_id, hl.name, hl.slug, hl.description_1, hl.description_2, 
            hl.short_description, hl.page_id, hl.sort_order, p.slug as page_slug
            FROM history_locations hl
            LEFT JOIN pages p ON hl.page_id = p.page_id
            WHERE hl.slug = :slug
            LIMIT 1'
        );

        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryLocations($row);
    }

    public function getLocationBySortOrder(int $sortOrder): ?HistoryLocation
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT history_location_id, name, slug, description_1, description_2, 
        short_description, page_id, sort_order
        FROM history_locations
        WHERE sort_order = :sort_order
        LIMIT 1'
        );

        $stmt->execute(['sort_order' => $sortOrder]);
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryLocations($row);
    }

    // IMAGES
    private function mapToHistoryImages(object $row): HistoryImage
    {
        $image = new HistoryImage();
        $image->id = (int)$row->history_image_id;
        $image->historyLocationId = (int)$row->history_location_id;
        $image->pageId = (int)$row->page_id;
        $image->eventId = (int)$row->event_id;
        $image->imageUrl = $row->image_url;
        $image->altText = $row->alt_text;
        $image->imageType = $row->image_type;
        $image->isPrimary = (bool)$row->is_primary;
        $image->sortOrder = $row->sort_order;

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
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImages($row);
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
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        return array_map([$this, 'mapToHistoryImages'], $rows);
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
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImages($row);
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
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImages($row);
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
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        return array_map([$this, 'mapToHistoryImages'], $rows);
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
        $row = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$row) {
            return null;
        }
        return $this->mapToHistoryImages($row);
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
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $pageId = null;
        $blocks = [];
        foreach ($rows as $row) {
            $pageId = (int)$row->page_id;
            $blocks[$row->block_type] = [
                'block_id' => (int)$row->block_id,
                'content' => json_decode($row->content_json, true),
                'sort_order' => $row->sort_order
            ];
        }
        return [
            'page_id' => $pageId,
            'blocks' => $blocks
        ];
    }

    public function getPageBlocksList(string $slug): array
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
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        $pageId = null;
        $blocks = [];

        foreach ($rows as $row) {
            $pageId = (int)$row->page_id;
            // Each block is stored as a numbered element — no overwriting
            $blocks[] = [
                'block_id' => (int)$row->block_id,
                'block_type' => $row->block_type,
                'content' => json_decode($row->content_json, true),
                'sort_order' => $row->sort_order,
            ];
        }
        return [
            'page_id' => $pageId,
            'blocks' => $blocks,
        ];
    }
}