<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\EventTypeRepositoryInterface;
use App\Core\Repository;
use App\Models\EventType;
use PDO;

final class EventTypeRepository extends Repository implements EventTypeRepositoryInterface
{
    public function getAllIdsOrdered(): array
    {
        $stmt = $this->db->query('SELECT event_type_id FROM event_types ORDER BY event_type_id');

        return array_map(static fn ($id): int => (int) $id, $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function getAllWithDisplay(): array
    {
        $stmt = $this->db->query(
            'SELECT event_type_id, name, description, card_image, info_path FROM event_types ORDER BY event_type_id'
        );

        $types = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $types[] = $this->mapRowToEventType($row);
        }

        return $types;
    }

    private function mapRowToEventType(array $row): EventType
    {
        $type = new EventType();
        $type->id = (int) $row['event_type_id'];
        $type->name = (string) $row['name'];
        $type->description = isset($row['description']) ? (string) $row['description'] : '';
        $type->cardImage = $this->nullableText($row, 'card_image');
        $type->infoPath = $this->infoPath($row);

        return $type;
    }

    private function nullableText(array $row, string $key): ?string
    {
        $value = isset($row[$key]) ? (string) $row[$key] : '';

        return $value !== '' ? $value : null;
    }

    private function infoPath(array $row): string
    {
        $value = isset($row['info_path']) ? (string) $row['info_path'] : '';

        return $value !== '' ? $value : '#';
    }
}
