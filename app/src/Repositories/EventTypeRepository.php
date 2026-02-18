<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

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
}
