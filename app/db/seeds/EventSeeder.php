<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seeds venues and events. Safe to re-run: clears events & venues first, then inserts.
 * Run: phinx seed:run -s EventSeeder
 * No migration needed — tables already exist. This is data only.
 */
class EventSeeder extends AbstractSeed
{
    public function run(): void
    {
        $adapter = $this->table('venues')->getAdapter();
        $adapter->execute('SET FOREIGN_KEY_CHECKS = 0');
        $adapter->execute('TRUNCATE TABLE events');
        $adapter->execute('TRUNCATE TABLE venues');
        $adapter->execute('SET FOREIGN_KEY_CHECKS = 1');

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
            [
                'name' => 'Lichtfabriek',
                'address' => 'Industrieweg 38',
                'city' => 'Haarlem',
                'capacity' => 1500,
            ],
            [
                'name' => 'Jopenkerk',
                'address' => 'Gedempte Voldersgracht 2',
                'city' => 'Haarlem',
                'capacity' => 800,
            ],
            [
                'name' => 'Caprera Openluchttheater',
                'address' => 'Hoge Duin en Daalseweg 2',
                'city' => 'Bloemendaal',
                'capacity' => 2500,
            ],
            [
                'name' => 'Slachthuis',
                'address' => 'Kleine Houtweg 18',
                'city' => 'Haarlem',
                'capacity' => 1200,
            ],
            [
                'name' => 'XO the Club',
                'address' => 'Lange Veerstraat 4',
                'city' => 'Haarlem',
                'capacity' => 600,
            ],
        ])->saveData();

        $this->table('events')->insert([
            // Friday dance (5)
            [
                'event_type_id' => 1,
                'venue_id' => 1,
                'title' => 'Opening Dance Night',
                'description' => 'Kickoff festival with live dance performances.',
                'event_day' => 'friday',
                'start_time' => '20:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 4,
                'title' => 'Tiësto — Club Session',
                'description' => 'An intimate club night featuring Tiësto\'s signature trance, techno, and electro classics.',
                'event_day' => 'friday',
                'start_time' => '22:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 5,
                'title' => 'Hardwell — Exclusive Club Night',
                'description' => 'A powerful dance and house set from Hardwell inside a historic Haarlem location.',
                'event_day' => 'friday',
                'start_time' => '23:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 4,
                'title' => 'Nicky Romero & Afrojack — Back2Back Session',
                'description' => 'A massive house & electro B2B performance by two Dutch superstars.',
                'event_day' => 'friday',
                'start_time' => '22:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 2,
                'title' => 'Armin van Buuren — Tech Trance Set',
                'description' => 'A signature Armin set blending trance melodies with modern techno drops.',
                'event_day' => 'friday',
                'start_time' => '22:00',
            ],
            // Saturday dance (4)
            [
                'event_type_id' => 1,
                'venue_id' => 6,
                'title' => 'Hardwell / Garrix / Armin – B2B2B Outdoor Show',
                'description' => 'Three headliners for a once-in-a-lifetime outdoor B2B set in Haarlem\'s forest amphitheater.',
                'event_day' => 'saturday',
                'start_time' => '14:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 5,
                'title' => 'Afrojack — Club Night',
                'description' => 'A high-energy house set with Afrojack\'s signature bass-driven festival sound.',
                'event_day' => 'saturday',
                'start_time' => '22:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 7,
                'title' => 'TiëstoWorld — Special Career Show',
                'description' => 'A unique Tiësto experience featuring classics, remixes, and special guest appearances.',
                'event_day' => 'saturday',
                'start_time' => '21:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 7,
                'title' => 'Nicky Romero – Late Night Club Set',
                'description' => 'Romero brings electrohouse and progressive anthems to this intimate midnight session.',
                'event_day' => 'saturday',
                'start_time' => '23:00',
            ],
            // Sunday dance (4)
            [
                'event_type_id' => 1,
                'venue_id' => 6,
                'title' => 'Afrojack / Tiësto / Nicky Romero — B2B2B Session',
                'description' => 'A massive daytime show featuring three global icons performing together on one stage.',
                'event_day' => 'sunday',
                'start_time' => '14:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 5,
                'title' => 'Armin van Buuren — Trance Club Set',
                'description' => 'A powerful, emotional trance performance from Armin inside a historic church venue.',
                'event_day' => 'sunday',
                'start_time' => '19:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 8,
                'title' => 'Hardwell — Final Night Club Show',
                'description' => 'Hardwell closes the festival with a dance-heavy, high-energy finale.',
                'event_day' => 'sunday',
                'start_time' => '21:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 7,
                'title' => 'Martin Garrix — Club Session',
                'description' => 'A rare, intimate Garrix set delivering big-room energy in a small venue.',
                'event_day' => 'sunday',
                'start_time' => '18:00',
            ],

            // History events (4 days)
            [
                'event_type_id' => 3,
                'venue_id' => 2,
                'title' => 'A Stroll Through History',
                'description' => 'Discover Haarlem\'s rich heritage through a guided walking tour.',
                'event_day' => 'thursday',
                'start_time' => '10:00',
            ],
            [
                'event_type_id' => 3,
                'venue_id' => 2,
                'title' => 'A Stroll Through History',
                'description' => 'Discover Haarlem\'s rich heritage through a guided walking tour.',
                'event_day' => 'friday',
                'start_time' => '10:00',
            ],
            [
                'event_type_id' => 3,
                'venue_id' => 2,
                'title' => 'A Stroll Through History',
                'description' => 'Discover Haarlem\'s rich heritage through a guided walking tour.',
                'event_day' => 'saturday',
                'start_time' => '10:00',
            ],
            [
                'event_type_id' => 3,
                'venue_id' => 2,
                'title' => 'A Stroll Through History',
                'description' => 'Discover Haarlem\'s rich heritage through a guided walking tour.',
                'event_day' => 'sunday',
                'start_time' => '10:00',
            ],
            // Other categories (for homepage one-per-category)
            [
                'event_type_id' => 2,
                'venue_id' => 2,
                'title' => 'Jazz Evening',
                'description' => 'Smooth jazz in the heart of Haarlem.',
                'event_day' => 'friday',
                'start_time' => '20:00',
            ],
            [
                'event_type_id' => 4,
                'venue_id' => 3,
                'title' => 'Food Market',
                'description' => 'Taste food from all around the world.',
                'event_day' => 'friday',
                'start_time' => '12:00',
            ],
            [
                'event_type_id' => 5,
                'venue_id' => 1,
                'title' => 'Stories of Haarlem',
                'description' => 'Immersive storytelling and cultural moments.',
                'event_day' => 'friday',
                'start_time' => '19:00',
            ],
        ])->saveData();
    }
}
