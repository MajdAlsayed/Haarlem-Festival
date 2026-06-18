<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ArtistsRepositoryInterface;
use App\Core\Repository;
use PDO;

class ArtistsRepository extends Repository implements ArtistsRepositoryInterface
{
    public function getAllOrdered(): array
    {
        $stmt = $this->db->query('SELECT name, slug, bio, image_filename FROM artists ORDER BY sort_order');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row): array => $this->mapListRow($row), $rows);
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, bio, image_filename FROM artists WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->mapDetailRow($row);
    }

    public function getPhotoFilenamesByArtistId(int $artistId): array
    {
        $stmt = $this->db->prepare('SELECT filename FROM artist_photos WHERE artist_id = :id ORDER BY sort_order');
        $stmt->execute(['id' => $artistId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row): string => (string) $row['filename'], $rows);
    }

    private function mapListRow(array $row): array
    {
        return [
            'name' => (string) $row['name'],
            'slug' => $this->nullableSlug($row),
            'bio' => isset($row['bio']) ? (string) $row['bio'] : null,
            'image' => (string) $row['image_filename'],
        ];
    }

    private function mapDetailRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => $this->nullableSlug($row),
            'bio' => isset($row['bio']) ? (string) $row['bio'] : null,
            'image' => (string) $row['image_filename'],
        ];
    }

    private function nullableSlug(array $row): ?string
    {
        if (!isset($row['slug']) || $row['slug'] === null || $row['slug'] === '') {
            return null;
        }

        return (string) $row['slug'];
    }
}
