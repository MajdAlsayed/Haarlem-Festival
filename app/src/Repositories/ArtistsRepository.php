<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class ArtistsRepository
{
    /**
     * @return array<int, array{name: string, bio: string|null, image_filename: string}>
     */
    public function getAllOrdered(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT name, bio, image_filename FROM artists ORDER BY sort_order');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn ($r) => [
            'name' => $r['name'],
            'bio' => $r['bio'],
            'image' => $r['image_filename'],
        ], $rows);
    }
}
