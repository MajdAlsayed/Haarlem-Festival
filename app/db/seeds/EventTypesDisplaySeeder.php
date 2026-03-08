<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class EventTypesDisplaySeeder extends AbstractSeed
{
    public function run(): void
    {
        $adapter = $this->table('event_types')->getAdapter();
        $adapter->execute("UPDATE event_types SET card_image = 'music.jpg', info_path = '/dance' WHERE name = 'dance'");
        $adapter->execute("UPDATE event_types SET card_image = 'jazz.jpg', info_path = '#' WHERE name = 'jazz'");
        $adapter->execute("UPDATE event_types SET card_image = 'history.png', info_path = '#' WHERE name = 'history'");
        $adapter->execute("UPDATE event_types SET card_image = 'food.jpg', info_path = '/food' WHERE name = 'yammy'");
        $adapter->execute("UPDATE event_types SET card_image = 'stories.jpg', info_path = '#' WHERE name = 'stories'");
    }
}
