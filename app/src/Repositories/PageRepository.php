<?php

namespace App\Repositories;

use App\Contracts\PageRepositoryInterface;
use App\Core\Database;
use App\Models\Page;

/** pages table lookup by slug. Used by PageService. */
class PageRepository implements PageRepositoryInterface
{
    public function getBySlug(string $slug): ?Page
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT page_id, slug, title
             FROM pages
             WHERE slug = :slug AND is_published = 1'
        );

        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToPage($row);
    }

    /** @return list<array{page_id:int,slug:string,title:string,is_published:bool}> All pages for CMS admin. */
    public function getAllForAdmin(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query(
            'SELECT page_id, slug, title, is_published
             FROM pages
             ORDER BY title ASC'
        );
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(static function ($r) {
            return [
                'page_id' => (int) $r['page_id'],
                'slug' => (string) $r['slug'],
                'title' => (string) $r['title'],
                'is_published' => (bool) $r['is_published'],
            ];
        }, $rows);
    }

    /** Get page by id for admin edit. Returns array with page_id, slug, title, is_published or null. */
    public function getById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT page_id, slug, title, is_published
             FROM pages
             WHERE page_id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return [
            'page_id' => (int) $row['page_id'],
            'slug' => (string) $row['slug'],
            'title' => (string) $row['title'],
            'is_published' => (bool) $row['is_published'],
        ];
    }

    /** Update page title, slug, is_published. */
    public function update(int $id, string $title, string $slug, bool $isPublished): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
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
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'SELECT page_id, slug, title FROM pages WHERE slug = :slug LIMIT 1'
        );
        $stmt->execute(['slug' => strtolower(trim($slug))]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ? $this->mapRowToPage($row) : null;
    }

    public function updateTitleBySlug(string $slug, string $title): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare(
            'UPDATE pages SET title = :title WHERE slug = :slug LIMIT 1'
        );

        return $stmt->execute([
            'title' => $title,
            'slug' => strtolower(trim($slug)),
        ]);
    }

    /** DB row → Page (private). */
    private function mapRowToPage(array $row): Page
    {
        $page = new Page();
        $page->id = (int) $row['page_id'];
        $page->slug = $row['slug'] ?? '';
        $page->title = $row['title'] ?? '';
        $page->content = '';

        return $page;
    }
}
