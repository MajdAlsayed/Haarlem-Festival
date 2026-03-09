<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\EventType;

class EventTypeRepository
{
    /**
     * Returns event_type_id values in table order (used for homepage category order).
     *
     * @return int[]
     */
    public function getAllIdsOrdered(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT event_type_id FROM event_types ORDER BY event_type_id');
        $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return array_map('intval', $rows);
    }

    /**
     * Returns all event types with display fields (for homepage category cards).
     *
     * @return EventType[]
     */
    public function getAllWithDisplay(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            'SELECT event_type_id, name, description, card_image, info_path FROM event_types ORDER BY event_type_id'
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $types = [];
        foreach ($rows as $row) {
            $type = new EventType();
            $type->id = (int) $row['event_type_id'];
            $type->name = $row['name'];
            $type->description = $row['description'];
            $type->cardImage = !empty($row['card_image']) ? $row['card_image'] : null;
            $type->infoPath = !empty($row['info_path']) ? $row['info_path'] : '#';
            $types[] = $type;
        }
        return $types;
    }
}
