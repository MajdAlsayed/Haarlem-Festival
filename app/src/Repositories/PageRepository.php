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