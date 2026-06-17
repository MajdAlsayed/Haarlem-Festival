<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\PhotosRepositoryInterface;
use App\Core\Repository;
use PDO;

final class PhotosRepository extends Repository implements PhotosRepositoryInterface
{
    public function getByContext(string $context): array
    {
        $stmt = $this->db->prepare('SELECT `key`, filename FROM site_photos WHERE context = :context ORDER BY sort_order');
        $stmt->execute(['context' => $context]);

        return array_map(fn (array $row): array => $this->mapPhotoRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getFilename(string $context, ?string $key = null): ?string
    {
        if ($key !== null) {
            $stmt = $this->db->prepare('SELECT filename FROM site_photos WHERE context = :context AND `key` = :key LIMIT 1');
            $stmt->execute(['context' => $context, 'key' => $key]);
        } else {
            $stmt = $this->db->prepare('SELECT filename FROM site_photos WHERE context = :context ORDER BY sort_order LIMIT 1');
            $stmt->execute(['context' => $context]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return isset($row['filename']) ? (string) $row['filename'] : null;
    }

    public function getFilenamesByContext(string $context): array
    {
        $stmt = $this->db->prepare('SELECT filename FROM site_photos WHERE context = :context ORDER BY sort_order');
        $stmt->execute(['context' => $context]);

        return array_map(fn (array $row): string => (string) $row['filename'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function mapPhotoRow(array $row): array
    {
        return [
            'key' => isset($row['key']) ? (string) $row['key'] : null,
            'filename' => (string) $row['filename'],
        ];
    }
}
