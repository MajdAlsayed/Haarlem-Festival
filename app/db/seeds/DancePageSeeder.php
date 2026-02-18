<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Dance PAGE seeder only.
 *
 * This adds one row to the `pages` table: slug = 'dance', title = 'Dance'.
 * That way the app knows the "Dance" page exists (e.g. for nav, routing).
 *
 * The 3 featured event CARDS on the Dance page do NOT come from here.
 * They come from the `events` table: events where event_type = 'dance'
 * are seeded by EventSeeder (event_type_id = 1). So to have 3 dance cards,
 * EventSeeder must insert at least 3 dance events.
 *
 * When you need page_id (e.g. for page_blocks), fetch by slug — never hardcode IDs.
 */
class DancePageSeeder extends AbstractSeed
{
    public function run(): void
    {
        $sql = "INSERT IGNORE INTO pages (slug, title, is_published) VALUES ('dance', 'Dance', 1)";
        $this->table('pages')->getAdapter()->execute($sql);
    }
}
