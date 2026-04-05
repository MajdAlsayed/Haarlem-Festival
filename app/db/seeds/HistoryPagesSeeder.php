<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryPagesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->execute("
            INSERT IGNORE INTO pages (slug, title, is_published) VALUES
            ('history', 'A Stroll Through History', 1),
            ('history-locations', 'Historic Locations of Haarlem', 1),
            ('history-st-bavo', 'Church of St. Bavo', 1),
            ('history-grote-markt', 'Grote Markt', 1),
            ('history-tours', 'Guided Walking Tour', 1)
        ");

        $this->execute("
            UPDATE history_locations 
            SET page_id = (SELECT page_id FROM pages WHERE slug = 'history-st-bavo')
            WHERE slug = 'st-bavo'
");

        $this->execute("
            UPDATE history_locations 
            SET page_id = (SELECT page_id FROM pages WHERE slug = 'history-grote-markt')
            WHERE slug = 'grote-markt'
");
    }
}
