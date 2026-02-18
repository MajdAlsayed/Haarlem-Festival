<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class MenuRepository
{
    /**
     * @return array<int, array{path: string, label: string}>
     */
    public function getNavLinks(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT path, label FROM menu_items ORDER BY sort_order');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn ($r) => ['path' => $r['path'], 'label' => $r['label']], $rows);
    }
}
