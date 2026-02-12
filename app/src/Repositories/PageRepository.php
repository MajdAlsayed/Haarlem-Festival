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
            'SELECT page_id, slug, title
             FROM pages
             WHERE slug = :slug AND is_published = 1'
        );

        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $page = new Page();
        $page->id = $row['page_id'];
        $page->slug = $row['slug'];
        $page->title = $row['title'];
        $page->content = ''; 

        return $page;
    }
}