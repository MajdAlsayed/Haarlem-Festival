<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryLocationsBlocksSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Get page id for locations page
        $locationsPage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history-locations'");
        $pageId = $locationsPage['page_id'];
        $this->execute("DELETE FROM page_blocks WHERE page_id = $pageId");

        // Get hero image by page id
        $heroImage = $this->fetchRow(
            "SELECT history_image_id FROM history_images 
         WHERE page_id = $pageId AND image_type = 'hero' LIMIT 1"
        );
        $heroImageId = $heroImage ? $heroImage['history_image_id'] : null;

        // Get all location id in sort order
        $locations = $this->fetchAll("SELECT history_location_id FROM history_locations ORDER BY sort_order");

        // Сards array build
        $cards = array_map(fn($loc) => [
            'location_id' => $loc['history_location_id'],
            'button_text' => 'READ MORE',
        ], $locations);

        // Insert page blocks for history locations page
        $this->table('page_blocks')->insert([
            [
                'page_id' => $pageId,
                'block_type' => 'hero',
                'content_json' => json_encode([
                    'title' => 'HISTORIC LANDMARKS OF HAARLEM',
                    'image_id' => $heroImageId
                ]),
                'sort_order' => 1
            ],
            [
                'page_id' => $pageId,
                'block_type' => 'about_banner',
                'content_json' => json_encode([
                    'text' => 'An encyclopedic guide to the monuments, buildings, and spaces that tell the story of Haarlem '.
                        'from medieval origins through the Golden Age to the present day'
                ]),
                'sort_order' => 2
            ],
            [
                'page_id' => $pageId,
                'block_type' => 'location_cards',
                'content_json' => json_encode([
                    'cards' => $cards
                ]),
                'sort_order' => 3
            ],
        ])->saveData();
    }
}
