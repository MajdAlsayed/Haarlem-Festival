<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ArtistsRepositoryInterface;
use App\Core\Repository;

/** Reads artists + artist_photos; used by ArtistService. */
class ArtistsRepository extends Repository implements ArtistsRepositoryInterface
{
    /** @return array<int, array{name: string, slug: string|null, bio: string|null, image: string}> */
    public function getAllOrdered(): array
    {
        $stmt = $this->db->query('SELECT name, slug, bio, image_filename FROM artists ORDER BY sort_order');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn ($r) => [
            'name' => $r['name'],
            'slug' => $r['slug'] ?? null,
            'bio' => $r['bio'],
            'image' => $r['image_filename'],
        ], $rows);
    }

    /** @return array{id: int, name: string, slug: string|null, bio: string|null, image: string}|null */
    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, slug, bio, image_filename FROM artists WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]); // prepared = safe from injection
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$r) {
            return null;
        }
        return [
            'id' => (int) $r['id'],
            'name' => $r['name'],
            'slug' => $r['slug'] ?? null,
            'bio' => $r['bio'],
            'image' => $r['image_filename'],
        ];
    }

    /** Gallery + career image filenames for one artist. @return list<string> */
    public function getPhotoFilenamesByArtistId(int $artistId): array
    {
        $stmt = $this->db->prepare('SELECT filename FROM artist_photos WHERE artist_id = :id ORDER BY sort_order');
        $stmt->execute(['id' => $artistId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn ($r) => $r['filename'], $rows);
    }
}
