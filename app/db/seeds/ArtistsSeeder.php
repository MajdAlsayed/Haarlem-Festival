<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class ArtistsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $adapter = $this->table('artists')->getAdapter();
        $adapter->execute('SET FOREIGN_KEY_CHECKS = 0');
        $adapter->execute('TRUNCATE TABLE event_artists');
        $adapter->execute('TRUNCATE TABLE artist_photos');
        $adapter->execute('TRUNCATE TABLE artists');
        $adapter->execute('SET FOREIGN_KEY_CHECKS = 1');
        $data = [
            ['name' => 'Robbert Hardwell', 'slug' => 'hardwell', 'bio' => 'A high-energy dance night featuring Hardwell\'s signature big-room sound, explosive drops, and immersive festival-style atmosphere.', 'image_filename' => 'Artist/hardwell1.png', 'sort_order' => 1],
            ['name' => 'Tiësto', 'slug' => 'tiesto', 'bio' => 'A signature Tiësto club night featuring his blend of trance, techno, and electronic energy inside Haarlem\'s Slachthuis.', 'image_filename' => 'Image (Tiësto).png', 'sort_order' => 2],
        ];
        $this->table('artists')->insert($data)->saveData();
    }
}
