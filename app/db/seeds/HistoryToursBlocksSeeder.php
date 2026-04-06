<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryToursBlocksSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Find the history-tours page
        $toursPage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history-tours'");
        $pageId = $toursPage['page_id'];

        // Clear old blocks for this page
        $this->execute("DELETE FROM page_blocks WHERE page_id = $pageId");

        // Find hero image for tours page
        $heroImage = $this->fetchRow(
            "SELECT history_image_id FROM history_images 
             WHERE page_id = $pageId AND image_type = 'hero' LIMIT 1"
        );
        $heroImageId = $heroImage ? $heroImage['history_image_id'] : null;

        $this->table('page_blocks')->insert([

            // Hero
            [
                'page_id' => $pageId,
                'block_type' => 'hero',
                'content_json' => json_encode([
                    'title' => 'GUIDED WALKING TOUR',
                    'subtitle' => "Experience Haarlem's history with expert local guides",
                    'image_id' => $heroImageId
                ]),
                'sort_order' => 1
            ],

            // Important information cards
            [
                'page_id' => $pageId,
                'block_type' => 'info_cards',
                'content_json' => json_encode([
                    'title' => 'IMPORTANT INFORMATION',
                    'cards' => [
                        [
                            'title' => 'Age Restriction',
                            'text' => 'This tour is suitable for ages 12 and up. Children under 12 cannot participate.',
                            'items' => []
                        ],
                        [
                            'title' => 'No Strollers',
                            'text' => 'Strollers cannot be accommodated due to narrow historic streets.',
                            'items' => []
                        ],
                        [
                            'title' => 'Weather',
                            'text' => 'The tour operates in all weather conditions. Please dress accordingly.',
                            'items' => []
                        ],
                        [
                            'title' => 'What to Bring',
                            'text' => '',
                            'items' => ['Comfortable shoes', 'Weather clothing', 'Water bottle']
                        ],
                    ]
                ]),
                'sort_order' => 2
            ],

            // Tour details (right side)
            [
                'page_id' => $pageId,
                'block_type' => 'tour_details',
                'content_json' => json_encode([
                    'title' => 'TOUR DETAILS',
                    'items' => [
                        ['label' => 'Duration', 'value' => '2.5 hours of guided exploration'],
                        ['label' => 'Group Size', 'value' => 'Maximum 12 people for personal attention'],
                        ['label' => 'Meeting Point', 'value' => 'Church of St. Bavo, Grote Markt'],
                        ['label' => 'Languages', 'value' => 'English, Dutch and Chinese available'],
                        ['label' => 'Includes', 'value' => 'Expert guide, 1 complimentary drink, 15min break'],
                    ]
                ]),
                'sort_order' => 3
            ],

            // Tickets options
            [
                'page_id' => $pageId,
                'block_type' => 'ticket_options',
                'content_json' => json_encode([
                    'title' => 'WHAT\'S INCLUDED',
                    'tickets' => [
                        [
                            'name' => 'Regular Ticket',
                            'price' => '€17,5',
                            'per' => 'person',
                            'items' => [
                                '2.5 hours guided tour',
                                '1 complimentary drink',
                                'Expert local guide',
                                'Small group experience',
                            ]
                        ],
                        [
                            'name' => 'Family Ticket',
                            'price' => '€60',
                            'per' => 'up to 4 people',
                            'items' => [
                                'All regular ticket benefits',
                                'Up to 4 people (ages 12+)',
                                'Perfect for families and friends',
                            ]
                        ],
                    ]
                ]),
                'sort_order' => 4
            ],
        ])->saveData();
    }
}
