<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class EventSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('venues')->insert([
            [
                'name' => 'Main Stage',
                'address' => 'Grote Markt 1',
                'city' => 'Haarlem',
                'capacity' => 5000,
            ],
            [
                'name' => 'City Center',
                'address' => 'Centrum',
                'city' => 'Haarlem',
                'capacity' => 3000,
            ],
            [
                'name' => 'Open Air',
                'address' => 'Parklaan 10',
                'city' => 'Haarlem',
                'capacity' => 2000,
            ],
        ])->saveData();

        $this->table('events')->insert([
            [
                'event_type_id' => 1, 
                'venue_id' => 1,      
                'title' => 'Opening Dance Night',
                'description' => 'Kickoff festival with live dance performances.'
            ],
            [
                'event_type_id' => 2,
                'venue_id' => 2,      
                'title' => 'Jazz Evening',
                'description' => 'Smooth jazz in the heart of Haarlem.'
            ],
            [
                'event_type_id' => 4, 
                'venue_id' => 3,      
                'title' => 'Food Market',
                'description' => 'Taste food from all around the world.'
            ],
            [
                'event_type_id' => 3, 
                'venue_id' => 2,
                'title' => 'Historic City Tour',
                'description' => 'Explore centuries of stories, architecture, and heritage.'
            ],
            [
                'event_type_id' => 5,
                'venue_id' => 1,
                'title' => 'Stories of Haarlem',
                'description' => 'Immersive storytelling and cultural moments.'
            ],
        ])->saveData();
    }
}
