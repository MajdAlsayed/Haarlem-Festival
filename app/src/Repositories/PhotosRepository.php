<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Repository;

/** site_photos: hero, schedule, event detail, music section (context + key). */
class PhotosRepository extends Repository
{
    /** @return list<array{key: string|null, filename: string}> */
    public function getByContext(string $context): array
    {
        $stmt = $this->db->prepare('SELECT `key`, filename FROM site_photos WHERE context = :context ORDER BY sort_order');
        $stmt->execute(['context' => $context]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn ($r) => [
            'key' => $r['key'],
            'filename' => $r['filename'],
        ], $rows);
    }

    /** key = null → first row for context (by sort_order). */
    public function getFilename(string $context, ?string $key = null): ?string
    {
        if ($key !== null) {
            $stmt = $this->db->prepare('SELECT filename FROM site_photos WHERE context = :context AND `key` = :key LIMIT 1');
            $stmt->execute(['context' => $context, 'key' => $key]);
        } else {
            $stmt = $this->db->prepare('SELECT filename FROM site_photos WHERE context = :context ORDER BY sort_order LIMIT 1');
            $stmt->execute(['context' => $context]);
        }
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $r ? $r['filename'] : null;
    }

    /** @return list<string> */
    public function getFilenamesByContext(string $context): array
    {
        $stmt = $this->db->prepare('SELECT filename FROM site_photos WHERE context = :context ORDER BY sort_order');
        $stmt->execute(['context' => $context]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn ($r) => $r['filename'], $rows);
    }
}
