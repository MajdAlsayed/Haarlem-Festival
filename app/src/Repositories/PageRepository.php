<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Page;

class PageRepository
{
    public function getBySlug(string $slug): ?Page
    {
        $db = Database::getConnection();

        $stmt = $db->prepare(
            'SELECT id, slug, title, content
             FROM pages
             WHERE slug = :slug AND is_published = 1'
        );

        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $page = new Page();
        $page->id = $row['id'];
        $page->slug = $row['slug'];
        $page->title = $row['title'];
        $page->content = $row['content'];

        return $page;
    }
}
