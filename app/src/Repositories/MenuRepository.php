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
        try {
            $db = Database::getConnection();
            $stmt = $db->query(
                "SELECT path, label FROM menu_items WHERE path <> '/program' ORDER BY sort_order"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            $rows = [];
        }

        $links = array_map(
            static fn ($r) => ['path' => (string) $r['path'], 'label' => (string) $r['label']],
            $rows
        );

        if ($links !== []) {
            return $links;
        }

        /** @var list<array{path: string, label: string}> */
        return require __DIR__ . '/../Config/nav.php';
    }
}
