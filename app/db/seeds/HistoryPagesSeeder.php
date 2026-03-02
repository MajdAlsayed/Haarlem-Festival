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
            ('history-grote-markt', 'Grote Markt', 1)
        ");
    }
}
