<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryImagesSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Get page_id by slug
        $historyPage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history'");
        $locationsPage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history-locations'");
        $stBavoPage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history-st-bavo'");
        $grootePage = $this->fetchRow("SELECT page_id FROM pages WHERE slug = 'history-grote-markt'");

        $data = [];

        // HERO BANNERS FOR PAGES

        // Homepage
        $data[] = [
            'history_location_id' => null,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/homepage-hero.jpg',
            'alt_text' => 'Historic Haarlem cityscape with church tower',
            'image_type' => 'hero',
            'is_primary' => false,
            'sort_order' => 0
        ];

        // Locations overview
        $data[] = [
            'history_location_id' => null,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations-hero.jpg',
            'alt_text' => 'Overview of historic Haarlem landmarks',
            'image_type' => 'hero',
            'is_primary' => false,
            'sort_order' => 0
        ];

        // Tours page
        $data[] = [
            'history_location_id' => null,
            'page_id' => null,
            'event_id' => 4,
            'image_url' => '/images/history/tours-hero.jpg',
            'alt_text' => 'Guided walking tour through Haarlem',
            'image_type' => 'hero',
            'is_primary' => false,
            'sort_order' => 0
        ];

        // PRIMARY IMAGES FOR 9 LOCATIONS

        $locations = [
            1 => ['name' => 'Church of St. Bavo', 'slug' => 'st-bavo'],
            2 => ['name' => 'Grote Markt', 'slug' => 'grote-markt'],
            3 => ['name' => 'De Hallen', 'slug' => 'de-hallen'],
            4 => ['name' => 'Proveniershof', 'slug' => 'proveniershof'],
            5 => ['name' => 'Jopenkerk', 'slug' => 'jopenkerk'],
            6 => ['name' => 'Waalse Kerk', 'slug' => 'waalse-kerk'],
            7 => ['name' => 'Molen de Adriaan', 'slug' => 'molen-de-adriaan'],
            8 => ['name' => 'Amsterdamse Poort', 'slug' => 'amsterdamse-poort'],
            9 => ['name' => 'Hof van Bakenes', 'slug' => 'hof-van-bakenes']
        ];

        foreach ($locations as $locationId => $location) {
            $data[] = [
                'history_location_id' => $locationId,
                'page_id' => null,
                'event_id' => null,
                'image_url' => "/images/history/locations/{$location['slug']}-primary.jpg",
                'alt_text' => $location['name'],
                'image_type' => 'primary',
                'is_primary' => true,
                'sort_order' => 0
            ];
        }

        // ST. BAVO DETAIL PAGE

        // St. Bavo hero
        $data[] = [
            'history_location_id' => 1,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/st-bavo-hero.jpg',
            'alt_text' => 'Church of St. Bavo exterior view',
            'image_type' => 'hero',
            'is_primary' => false,
            'sort_order' => 0
            ];

        // St. Bavo gallery
        $data[] = [
            'history_location_id' => 1,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/st-bavo-gallery-1.jpg',
            'alt_text' => 'Architectural details of Church of St. Bavo',
            'image_type' => 'gallery',
            'is_primary' => false,
            'sort_order' => 1
        ];

        $data[] = [
            'history_location_id' => 1,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/st-bavo-gallery-2.jpg',
            'alt_text' => 'Interior of Church of St. Bavo with organ',
            'image_type' => 'gallery',
            'is_primary' => false,
            'sort_order' => 2
        ];

        // GROTE MARKT DETAIL PAGE

        // Grote Markt hero
        $data[] = [
            'history_location_id' => 2,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/grote-markt-hero.jpg',
            'alt_text' => 'Grote Markt square aerial view',
            'image_type' => 'hero',
            'is_primary' => false,
            'sort_order' => 0
        ];

        // Grote Markt gallery images
        $data[] = [
            'history_location_id' => 2,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/grote-markt-gallery-1.jpg',
            'alt_text' => 'Market day at Grote Markt',
            'image_type' => 'gallery',
            'is_primary' => false,
            'sort_order' => 1
        ];

        $data[] = [
            'history_location_id' => 2,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/grote-markt-gallery-2.jpg',
            'alt_text' => 'The statue of Laurens Janszoon Coster at Grote Markt',
            'image_type' => 'gallery',
            'is_primary' => false,
            'sort_order' => 2
        ];

        $data[] = [
            'history_location_id' => 2,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/grote-markt-gallery-3.jpg',
            'alt_text' => 'Old black and white image of Grote Markt',
            'image_type' => 'gallery',
            'is_primary' => false,
            'sort_order' => 3
        ];

        $data[] = [
            'history_location_id' => 2,
            'page_id' => null,
            'event_id' => null,
            'image_url' => '/images/history/locations/grote-markt-gallery-4.jpg',
            'alt_text' => 'Market at Grote Markt',
            'image_type' => 'gallery',
            'is_primary' => false,
            'sort_order' => 4
        ];
        $this->table('history_images')->insert($data)->saveData();
    }
}
