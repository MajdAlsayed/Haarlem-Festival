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
        $page->slug = $row['slug'];
        $page->title = $row['title'];
        $page->content = '';
        return $page;
    }
}