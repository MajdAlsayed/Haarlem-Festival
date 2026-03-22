<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryHomeBlocksSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['HistoryImagesSeeder'];
    }

    public function run(): void
    {
        // Ensure history page exists (HistoryPagesSeeder may not have run yet)
        $this->getAdapter()->execute(
            "INSERT IGNORE INTO pages (slug, title, is_published) VALUES ('history', 'A Stroll Through History', 1)"
        );
        // Get page id for history homepage
        $historyPage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history'");
        if (!$historyPage) {
            return;
        }
        $pageId = (int) $historyPage['page_id'];
        $this->execute("DELETE FROM page_blocks WHERE page_id = " . $pageId);

        // Get hero image dynamicaly
        $heroImage = $this->fetchRow(
            "SELECT history_image_id FROM history_images 
             WHERE page_id = $pageId AND image_type = 'hero' LIMIT 1"
        );
        $heroImageId = $heroImage ? $heroImage['history_image_id'] : null;

        // Insert page blocks for history homepage
        $this->table('page_blocks')->insert([
            [
                'page_id' => $pageId,
                'block_type' => 'hero',
                'content_json' => json_encode([
                    'title' => "A STROLL\nTHROUGH HISTORY",
                    'subtitle' => 'Discover 9 Landmarks That Shaped Haarlem',
                    'description' => 'Experience these sites on our guided walking tours',
                    'button_text' => 'EXPLORE TOURS',
                    'button_url' => '/history/tours',
                    'image_id' => $heroImageId
                ]),
                'sort_order' => 1
            ],
            [
                'page_id' => $pageId,
                'block_type' => 'about_banner',
                'content_json' => json_encode([
                    'title' => "HAARLEM'S RICH HISTORICAL HERITAGE",
                    'text' => 'Haarlem, the captivating capital of North Holland, has been a chartered city since 1245. '.
                        'During the Dutch Golden Age of the 17th century, it flourished as a center of art, culture, and '.
                        'commerce. Wealthy merchants commissioned grand buildings and patronized renowned artists like Frans '.
                        'Hals, whose legacy still resonates through the city\'s museums and galleries. The nine landmarks '.
                        'featured in our journey represent the essence of Haarlem\'s story: from medieval defenses and '.
                        'magnificent churches to unique hofjes that reflect the city\'s charitable traditions. Together, they '.
                        'paint a vivid picture of how this remarkable Dutch city evolved into the cultural treasure it is today.'
                ]),
                'sort_order' => 2
            ],
            [
                'page_id' => $pageId,
                'block_type' => 'section_header',
                'content_json' => json_encode([
                    'title' => '9 SITES THAT TELL HAARLEM\'S STORY',
                    'description' => 'From grand churches to hidden courtyards, each landmark offers a window into Haarlem\'s '.
                        'transformation from medieval town to cultural treasure.'
                ]),
                'sort_order' => 3
            ],
            [
                'page_id' => $pageId,
                'block_type' => 'location_cards',
                'content_json' => json_encode([
                    'cards' => [
                        ['location_id' => 1],
                        ['location_id' => 2],
                        ['location_id' => 3]
                    ],
                    'button_text' => 'EXPLORE ALL LANDMARKS',
                    'button_url' => '/history/locations'
                ]),
                'sort_order' => 4
            ],
            [
                'page_id' => $pageId,
                'block_type' => 'text_block',
                'content_json' => json_encode([
                    'title' => 'EXPERIENCE THIS HISTORY',
                    'description' => 'Join our expert-led walking tour to discover these landmarks in person. Our knowledgeable '.
                        'guides bring centuries of history to life with captivating stories and fascinating insights about '.
                        'Haarlem\'s remarkable past.',
                    'button_text' => 'VIEW TOUR DETAILS',
                    'button_url' => '/history/tours'
                ]),
                'sort_order' => 5
            ],
        ])->saveData();
    }
}
