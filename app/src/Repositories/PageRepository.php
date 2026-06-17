<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\PageRepositoryInterface;
use App\Core\Repository;
use App\Models\Page;
use PDO;

final class PageRepository extends Repository implements PageRepositoryInterface
{
    public function getBySlug(string $slug): ?Page
    {
        $stmt = $this->db->prepare(
            'SELECT page_id, slug, title
             FROM pages
             WHERE slug = :slug AND is_published = 1'
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->mapRowToPage($row);
    }

    public function getAllForAdmin(): array
    {
        $stmt = $this->db->query(
            'SELECT page_id, slug, title, is_published
             FROM pages
             ORDER BY title ASC'
        );

        return array_map(fn (array $row): array => $this->mapAdminRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT page_id, slug, title, is_published
             FROM pages
             WHERE page_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->mapAdminRow($row);
    }

    public function update(int $id, string $title, string $slug, bool $isPublished): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pages SET title = :title, slug = :slug, is_published = :pub
             WHERE page_id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $title,
            'slug' => $slug,
            'pub' => $isPublished ? 1 : 0,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function findBySlugForAdmin(string $slug): ?Page
    {
        $stmt = $this->db->prepare(
            'SELECT page_id, slug, title FROM pages WHERE slug = :slug LIMIT 1'
        );
        $stmt->execute(['slug' => $this->normalizeSlug($slug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->mapRowToPage($row);
    }

    public function updateTitleBySlug(string $slug, string $title): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pages SET title = :title WHERE slug = :slug LIMIT 1'
        );

        return $stmt->execute([
            'title' => $title,
            'slug' => $this->normalizeSlug($slug),
        ]);
    }

    private function mapRowToPage(array $row): Page
    {
        $page = new Page();
        $page->id = (int) $row['page_id'];
        $page->slug = $this->rowText($row, 'slug');
        $page->title = $this->rowText($row, 'title');
        $page->content = '';

        return $page;
    }

    private function mapAdminRow(array $row): array
    {
        return [
            'page_id' => (int) $row['page_id'],
            'slug' => (string) $row['slug'],
            'title' => (string) $row['title'],
            'is_published' => (bool) $row['is_published'],
        ];
    }

    private function normalizeSlug(string $slug): string
    {
        return strtolower(trim($slug));
    }

    private function rowText(array $row, string $key): string
    {
        return isset($row[$key]) ? (string) $row[$key] : '';
    }
}
