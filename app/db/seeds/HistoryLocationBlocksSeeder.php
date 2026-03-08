<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryLocationBlocksSeeder extends AbstractSeed
{
    public function run(): void
    {
        // --------
        // ST BAVO
        // --------

        $stBavoPage = $this->fetchRow(
            "SELECT page_id FROM pages WHERE slug = 'history-st-bavo'"
        );

        if (!$stBavoPage) {
            echo "Page 'history-st-bavo' not found, skipping.\n";
        } else {
            $pageId = $stBavoPage['page_id'];

            // Lookup hero image
            $stBavoHero = $this->fetchRow(
                "SELECT history_image_id FROM history_images
                 WHERE page_id = {$pageId} AND image_type = 'hero'
                 LIMIT 1"
            );
            $heroImgId = $stBavoHero ? $stBavoHero['history_image_id'] : null;

            // Lookup gallery image id
            $stBavoGallery1 = $this->fetchRow(
                "SELECT history_image_id FROM history_images
                 WHERE history_location_id = 1 AND image_type = 'gallery'
                 ORDER BY sort_order ASC LIMIT 1"
            );
            $stBavoGallery2 = $this->fetchRow(
                "SELECT history_image_id FROM history_images
                 WHERE history_location_id = 1 AND image_type = 'gallery'
                 ORDER BY sort_order ASC LIMIT 1 OFFSET 1"
            );

            $imgId1 = $stBavoGallery1 ? $stBavoGallery1['history_image_id'] : null;
            $imgId2 = $stBavoGallery2 ? $stBavoGallery2['history_image_id'] : null;

            $this->execute("DELETE FROM page_blocks WHERE page_id = {$pageId}");

            $this->table('page_blocks')->insert([

                // HERO
                [
                    'page_id' => $pageId,
                    'block_type' => 'hero',
                    'content_json' => json_encode([
                        'title' => 'CHURCH OF ST. BAVO',
                        'subtitle' => 'Grote Kerk - The Great Church of Haarlem',
                        'image_id' => $heroImgId,
                    ]),
                    'sort_order' => 0,
                ],
                // STATS BAR
                [
                    'page_id' => $pageId,
                    'block_type' => 'stats_bar',
                    'content_json' => json_encode([
                        'stats' => [
                            ['value' => '1370', 'label' => 'Construction Began'],
                            ['value' => '150', 'label' => 'Years to Complete'],
                            ['value' => '80m', 'label' => 'Tower Height'],
                            ['value' => '40m', 'label' => 'Nave Height'],
                            ['value' => '5,068', 'label' => 'Organ Pipes'],
                        ],
                    ]),
                    'sort_order' => 1,
                ],
                // CONTENT SECTION (image-right)
                [
                    'page_id' => $pageId,
                    'block_type' => 'content_section',
                    'content_json' => json_encode([
                        'layout' => 'image-right',
                        'title' => 'A MONUMENT TO FAITH & AMBITION',
                        'sections' => [
                            [
                                'subtitle' => 'Medieval Construction (1370-1520)',
                                'paragraphs' => [
                                    'The Church of St. Bavo rose on the site of an earlier Romanesque church that had served Haarlem since the 12th ' .
                                        'century. By 1370, the prosperous  trading city required a grander statement of civic and religious identity. ' .
                                        'Ambitious plans were drawn for a church that would rival Europe\'s great cathedrals.',
                                    'Construction proceeded slowly over 150 years, dependent on donations from wealthy merchants. The church was built ' .
                                        'from west to east, allowing services to continue during construction. This gradual process resulted in subtle stylistic ' .
                                        'evolution - from robust early Gothic in the western sections to refined late Gothic in the choir.',
                                    'Dedicated to St. Bavo, a 7th century nobleman who renounced wealth for faith, the completed church blazed with Catholic splendor. ' .
                                        'Its interior featured painted walls, gilded altars, stained glass, and over 30 side altars maintained by guilds and wealthy ' .
                                        'families competing to display their devotion and prosperity.',
                                ],
                            ],
                            [
                                'subtitle' => 'Protestant Transformation (1578)',
                                'paragraphs' => [
                                    'The Reformation shattered this Catholic world. In 1566, Calvinist iconoclasts stormed the church during the Beeldenstorm, ' .
                                        'destroying "idolatrous" imagery. After a brutal Spanish siege in 1572-1573, Protestant control was established in 1578.',
                                    'The transformation was dramatic: altars demolished, walls whitewashed, statues removed. The building was stripped of Catholic ' .
                                        'decoration in favor of Protestant simplicity. Yet the architecture survived intact. The church became a "preaching hall" ' .
                                        'where scripture took primacy over visual splendor, with massive wooden galleries installed for larger congregations.',
                                    'The church also became increasingly civic. Without an adequate town hall, it hosted municipal meetings and ceremonies, ' .
                                        'blurring the line between religious and civic space - a characteristic of Calvinist Netherlands.',
                                ],
                            ],
                        ],
                        'image_ids' => [$imgId1],
                        'did_you_know' => [
                            'The church contains grave stones of over 1,500 people buried beneath its floor, '.
                                'including painter Frans Hals (1666) and architect Lieven de Key (1627).',
                            'This practice continued until 1829 when public health concerns ended church burials.'
                        ],
                    ]),
                    'sort_order' => 2,
                ],
                // CONTENT SECTION (image-left)
                [
                    'page_id' => $pageId,
                    'block_type' => 'content_section',
                    'content_json' => json_encode([
                        'layout' => 'image-left',
                        'title' => 'MUSICAL LEGACY & PRESERVATION',
                        'sections' => [
                            [
                                'subtitle' => 'The Christian Müller Organ',
                                'paragraphs' => [
                                    'The crowning glory came in 1738 with the installation of the Christian Müller organ. This magnificent Baroque instrument, ' .
                                        'with over 5,000 pipes, is one of Europe\'s finest. Its ornate wooden case, featuring cherubs and musical instruments, ' .
                                        'brought decorative splendor back to the whitewashed space.',
                                    'The organ attracted musicians from across Europe. The 10-year-old Mozart played it in 1766, and later Mendelssohn, drawn by ' .
                                        'its legendary sound quality. Concerts became regular events, establishing a musical tradition that continues today through ' .
                                        'the annual International Organ Festival.',
                                ],
                            ],
                            [
                                'subtitle' => 'Preservation & Modern Role',
                                'paragraphs' => [
                                    'By the early 1800s, neglect threatened the church with demolition. The Romantic movement\'s appreciation for medieval architecture ' .
                                        'saved it - King William I personally intervened, and restoration began in 1839. The most recent comprehensive restoration ' .
                                        '(1985-2010) cost over €15 million, using modern technology to stabilize the structure while respecting historical integrity.',
                                    'Today, the church balances religious, cultural, and tourist functions. Protestant services continue, while concerts, exhibitions, ' .
                                        'and events generate income for ongoing maintenance. Over 200,000 visitors annually ensure this monument\'s preservation for future generations.',
                                ],
                            ],
                        ],
                        'image_ids' => [$imgId2],
                    ]),
                    'sort_order' => 3,
                ],
                // EXPERIENCE
                [
                    'page_id' => $pageId,
                    'block_type' => 'experience',
                    'content_json' => json_encode([
                        'title' => 'Experience St. Bavo today',
                        'independent' => [
                            'title' => 'INDEPENDENT VISIT',
                            'text' => 'The church is open to visitors daily for self-guided exploration. '.
                                'Climb the tower for panoramic views of Haarlem, examine the famous organ up close, '.
                                'and discover the building\'s architectural details at your own pace.',
                            'details' => [
                                ['label' => 'Hours:', 'text' => 'Monday-Saturday 10:00-17:00'],
                                ['label' => 'Admission:', 'text' => '€2.50 adults, children free'],
                                ['label' => 'Tower:', 'text' => 'Additional €5 (weather permitting)'],
                            ],
                        ],
                        'guided' => [
                            'title' => 'GUIDED WALKING TOUR',
                            'text' => 'St. Bavo serves as the starting point for our comprehensive "A Stroll through History" '.
                                'walking tour. Our expert guides provide deeper historical context and architectural analysis, '.
                                'connecting the church to Haarlem\'s broader story across 9 historic landmarks.',
                        ],
                    ]),
                    'sort_order' => 4,
                ],

            ])->saveData();

            echo "St. Bavo blocks seeded.\n";
        }

        // -----------
        // GROTE MARKT
        // -----------

        $grotePage = $this->fetchRow(
            "SELECT page_id FROM pages WHERE slug = 'history-grote-markt'"
        );

        if (!$grotePage) {
            echo "Page 'history-grote-markt' not found, skipping.\n";
        } else {
            $pageId = $grotePage['page_id'];

            // Lookup hero image
            $groteHero = $this->fetchRow(
                "SELECT history_image_id FROM history_images
                 WHERE page_id = {$pageId} AND image_type = 'hero'
                 LIMIT 1"
            );
            $heroImgId = $groteHero ? $groteHero['history_image_id'] : null;

            // Lookup gallery image IDs
            $groteGalleries = $this->fetchAll(
                "SELECT history_image_id FROM history_images
                 WHERE history_location_id = 2 AND image_type = 'gallery'
                 ORDER BY sort_order ASC"
            );
            $imgId1 = $groteGalleries[0]['history_image_id'] ?? null;
            $imgId2 = $groteGalleries[1]['history_image_id'] ?? null;
            $imgId3 = $groteGalleries[2]['history_image_id'] ?? null;
            $imgId4 = $groteGalleries[3]['history_image_id'] ?? null;

            $this->execute("DELETE FROM page_blocks WHERE page_id = {$pageId}");

            $this->table('page_blocks')->insert([

                // HERO
                [
                    'page_id' => $pageId,
                    'block_type' => 'hero',
                    'content_json' => json_encode([
                        'title' => 'GROTE MARKT',
                        'subtitle' => 'The Heart of Haarlem for Eight Centuries',
                        'image_id' => $heroImgId,
                    ]),
                    'sort_order' => 0,
                ],

                // ABOUT BANNER
                [
                    'page_id' => $pageId,
                    'block_type' => 'about_banner',
                    'content_json' => json_encode([
                        'text' => 'More than a marketplace, the Grote Markt has served as Haarlem\'s stage for commerce, '.
                            'justice, celebration, and civic life since the 13th century - a living testament to the Dutch '.
                            'urban tradition of the central square.',
                    ]),
                    'sort_order' => 1,
                ],

                // STATS BAR
                [
                    'page_id' => $pageId,
                    'block_type' => 'stats_bar',
                    'content_json' => json_encode([
                        'stats' => [
                            ['value' => '780+', 'label' => 'Years of History'],
                            ['value' => '10,000m2', 'label' => 'Square Area'],
                            ['value' => '100+', 'label' => 'Events Annually'],
                            ['value' => '4', 'label' => 'Major Buildings'],
                            ['value' => '2M+', 'label' => 'Annual Visitors'],
                        ],
                    ]),
                    'sort_order' => 2,
                ],

                // CONTENT SECTION (image-right)
                [
                    'page_id' => $pageId,
                    'block_type' => 'content_section',
                    'content_json' => json_encode([
                        'layout' => 'image-right',
                        'title' => 'THE BEATING HEART OF HAARLEM',
                        'sections' => [
                            [
                                'subtitle' => 'Medieval Origins: The Birth of Urban Space',
                                'paragraphs' => [
                                    'The Grote Markt was formalized in 1245 when Count Willem II granted Haarlem market privileges, transforming an informal '.
                                        'gathering place into the official heart of the growing town. Strategically positioned at the intersection of trade '.
                                        'routes connecting Amsterdam, Leiden, and the North Sea coast, the square became central to Haarlem\'s commercial success.',
                                    'Medieval urban planning placed the market at the town\'s core, adjacent to the main church and town hall. This arrangement '.
                                        'expressed the medieval worldview: church, civic authority, and commerce as the three pillars of society. The Grote Markt '.
                                        'exemplified this ideal, with the church dominating the eastern side and civic buildings the western.',
                                ],
                            ],
                            [
                                'subtitle' => 'Golden Age Prosperity',
                                'paragraphs' => [
                                    'The 17th century brought unprecedented prosperity to Haarlem, transforming the Grote Markt architecturally.The city\'s '.
                                        'success in linen bleaching, brewing, and painting generated wealth that demanded visible expression. Lieven de Key, '.
                                        'Haarlem\'s master architect, redesigned the Town Hall façade in 1602 in exuberant Dutch Renaissance style, balancing classical '.
                                        'order with decorative playfulness. Elaborate carvings, step-gables, and a central tower declared civic pride to all who entered the square.',
                                    'De Key\'s Meat Hall (Vleeshal, 1602-1603) demonstrated how commercial buildings could be architectural monuments. Rather than purely '.
                                        'functional, the ornate façade featured carved ox heads and butcher symbols - a fusion of commerce and art exemplifying the Dutch '.
                                        'Golden Age ethos that business success deserved beautiful architecture.',
                                    'The square became a showcase for urban prosperity. Wealthy merchants built impressive stepped-gable houses along the perimeter while '.
                                        'artists like Gerrit Berckheyde immortalized the scene in paintings now in major museums worldwide. Market activity grew increasingly '.
                                        'specialized with dedicated buildings for specific trades, reflecting Haarlem\'s evolution into a sophisticated commercial center.',
                                ],
                            ],
                        ],
                        'image_ids' => [$imgId1, $imgId2],
                        'did_you_know' => [
                            'The statue in the center honors Laurens Janszoon Coster, whom some Dutch historians credit with inventing movable type printing before Gutenberg.',
                            'This claim is disputed internationally, but the statue (1856) reflects 19th-century Dutch pride in asserting their contribution to civilization.',
                        ],
                    ]),
                    'sort_order' => 3,
                ],

                // CONTENT SECTION (image-left)
                [
                    'page_id' => $pageId,
                    'block_type' => 'content_section',
                    'content_json' => json_encode([
                        'layout' => 'image-left',
                        'title' => 'MODERN TRANSFORMATIONS',
                        'sections' => [
                            [
                                'subtitle' => 'Contemporary Revival: Pedestrianization',
                                'paragraphs' => [
                                    'Post-war prosperity brought automobile dominance. By the 1970s, the Grote Markt had become a parking lot, its historic stones buried '.
                                        'under asphalt and its grandeur obscured by parked cars. The Saturday market continued, but vehicles had claimed the square.',
                                    'The late 20th-century pedestrianization movement transformed Dutch city centers as urban planners recognized that cars degraded historic '.
                                        'spaces. Haarlem gradually removed vehicles from the Grote Markt - a decades-long process as merchants feared losing customers and '.
                                        'drivers resented inconvenience. By the early 2000s, the transformation was complete, sparking remarkable revival. Café terraces expanded, '.
                                        'the market flourished, and street performers and festivals proliferated.',
                                ],
                            ],
                            [
                                'subtitle' => 'The Square Today',
                                'paragraphs' => [
                                    'Today\'s Grote Markt hosts concerts, festivals, political demonstrations, and celebrations throughout the year. The Saturday market continues '.
                                        'its centuries-old tradition, though farmers now arrive by truck rather than cart, and organic vegetables replace medieval grain.',
                                    'Tourism has become economically crucial. Visitors photograph the square, relax at café terraces, and shop in surrounding stores. The square\'s '.
                                        'Renaissance beauty, shared globally through social media, attracts tourists whose spending funds heritage preservation through entrance fees, café taxes, and hotel revenue.',
                                    'The Grote Markt remains what it has always been: Haarlem\'s stage. The actors and performances change - medieval merchants become modern tourists, '.
                                        'public executions become jazz concerts, Catholic processions become pride parades - but the fundamental role persists. Eight centuries later, '.
                                        'the square is still where Haarlem gathers, celebrates, protests, and exists as a community.',
                                ],
                            ],
                        ],
                        'image_ids' => [$imgId3, $imgId4],
                    ]),
                    'sort_order' => 4,
                ],

                // EXPERIENCE
                [
                    'page_id' => $pageId,
                    'block_type' => 'experience',
                    'content_json' => json_encode([
                        'title' => 'Experience GROTE MARKT today',
                        'independent' => [
                            'title' => 'INDEPENDENT VISIT',
                            'text' => 'The Grote Markt is always open and accessible - it\'s a public square serving as Haarlem\'s central gathering space. Simply arrive and experience '.
                                'the layered history embedded in its architecture and spatial design.',
                            'subtitle' => 'Best Times to Visit',
                            'details' => [
                                ['label' => 'Saturday morning:', 'text' => 'Traditional market in full swing'],
                                ['label' => 'Weekday afternoons:', 'text' => 'Quieter, ideal for photography'],
                                ['label' => 'December:', 'text' => 'Christmas market transforms the square'],
                            ],
                        ],
                        'guided' => [
                            'title' => 'GUIDED WALKING TOUR',
                            'text' => 'The Grote Markt is a central stop on our "A Stroll through History" walking tour. Our guides explain the square\'s evolution, point out architectural '.
                                'details easily missed, and share stories that bring eight centuries of history to vivid life.',
                        ],
                    ]),
                    'sort_order' => 5,
                ],

            ])->saveData();

            echo "Grote Markt blocks seeded.\n";
        }
    }
}
