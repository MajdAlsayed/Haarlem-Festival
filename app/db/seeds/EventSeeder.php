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
            [
                'name' => 'Puncher Comedy Club',
                'address' => 'Grote Markt 12',
                'city' => 'Haarlem',
                'capacity' => 400,
            ],
        ])->saveData();

        $this->table('events')->insert([
            // Friday dance (5) – per Figma
            [
                'event_type_id' => 1,
                'venue_id' => 4,
                'title' => 'Nicky Romero & Afrojack – Back2Back Session',
                'description' => 'A massive house & electro B2B performance by two Dutch superstars.',
                'event_day' => 'friday',
                'start_time' => '20:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 7,
                'title' => 'Tiësto – Club Session',
                'description' => 'An intimate club night showcasing Tiësto\'s iconic blend of trance, techno, and electro.',
                'event_day' => 'friday',
                'start_time' => '22:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 5,
                'title' => 'Hardwell – Club Night',
                'description' => 'A high-energy dance and house set from Hardwell in a unique church-turned-club venue.',
                'event_day' => 'friday',
                'start_time' => '23:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 8,
                'title' => 'Armin van Buuren – Tech Trance Set',
                'description' => 'A signature Armin set blending trance melodies with modern techno drops.',
                'event_day' => 'friday',
                'start_time' => '22:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 9,
                'title' => 'Martin Garrix – Exclusive Club Show',
                'description' => 'A rare small-venue performance featuring Garrix\'s biggest future house & electronic hits.',
                'event_day' => 'friday',
                'start_time' => '22:00',
            ],
            // Saturday dance (4)
            [
                'event_type_id' => 1,
                'venue_id' => 6,
                'title' => 'Hardwell / Garrix / Armin - B2B2B Outdoor Show',
                'description' => 'Three headliners for a once-in-a-lifetime outdoor B2B set in Haarlem\'s forest amphitheater.',
                'event_day' => 'saturday',
                'start_time' => '14:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 5,
                'title' => 'Afrojack – Club Night',
                'description' => 'A high-energy house set with Afrojack\'s signature bass-driven festival sound.',
                'event_day' => 'saturday',
                'start_time' => '22:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 7,
                'title' => 'TiëstoWorld – Special Career Show',
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
                'title' => 'Afrojack / Tiësto / Nicky Romero - B2B2B Session',
                'description' => 'A massive daytime show featuring three global icons performing together on one stage.',
                'event_day' => 'sunday',
                'start_time' => '14:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 7,
                'title' => 'Martin Garrix – Club Session',
                'description' => 'A rare, intimate Garrix set delivering big-room energy in a small venue.',
                'event_day' => 'sunday',
                'start_time' => '18:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 5,
                'title' => 'Armin van Buuren – Trance Club Set',
                'description' => 'A powerful, emotional trance performance from Armin inside a historic church venue.',
                'event_day' => 'sunday',
                'start_time' => '19:00',
            ],
            [
                'event_type_id' => 1,
                'venue_id' => 8,
                'title' => 'Hardwell – Final Night Club Show',
                'description' => 'Hardwell closes the festival with a dance-heavy, high-energy finale.',
                'event_day' => 'sunday',
                'start_time' => '21:00',
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
                'event_type_id' => 4,
                'venue_id' => 3,
                'title' => 'Food Market',
                'description' => 'Taste food from all around the world.',
                'event_day' => 'friday',
                'start_time' => '12:00',
            ],
// Stories (event_type_id = 5)

[
    'event_type_id' => 5,
    'venue_id' => 10,
    'title' => 'Omdenken Podcast',
    'description' => 'Live podcast recording.',
    'event_day' => 'thursday',
    'start_time' => '16:00',
    'end_time' => '17:00',
],
[
    'event_type_id' => 5,
    'venue_id' => 11,
    'title' => 'Flip Thinking Podcast',
    'description' => 'Live discussion and storytelling.',
    'event_day' => 'thursday',
    'start_time' => '19:00',
    'end_time' => '20:15',
],
[
    'event_type_id' => 5,
    'venue_id' => 12,
    'title' => 'Podcastlast Haarlem Special',
    'description' => 'Special live podcast.',
    'event_day' => 'thursday',
    'start_time' => '20:30',
    'end_time' => '21:45',
],

[
    'event_type_id' => 5,
    'venue_id' => 13,
    'title' => 'De geschiedenis van familie ten Boom',
    'description' => 'Ten Boom family history.',
    'event_day' => 'friday',
    'start_time' => '16:00',
    'end_time' => '17:00',
],
[
    'event_type_id' => 5,
    'venue_id' => 10,
    'title' => 'Winnaars van verhalenvertel wedstrijd, verhalen voor Haarlem',
    'description' => 'Storytelling competition.',
    'event_day' => 'friday',
    'start_time' => '19:00',
    'end_time' => '20:30',
],
[
    'event_type_id' => 5,
    'venue_id' => 12,
    'title' => 'Het verhaal van de Oeserzwammerij',
    'description' => 'Local storytelling event.',
    'event_day' => 'friday',
    'start_time' => '19:00',
    'end_time' => '20:15',
],
[
    'event_type_id' => 5,
    'venue_id' => 11,
    'title' => 'The Story of Buurderij Haarlem',
    'description' => 'Community storytelling.',
    'event_day' => 'friday',
    'start_time' => '20:30',
    'end_time' => '21:45',
],

[
    'event_type_id' => 5,
    'venue_id' => 14,
    'title' => 'Mister Anansi',
    'description' => 'Family storytelling session.',
    'event_day' => 'saturday',
    'start_time' => '10:00',
    'end_time' => '11:00',
],
[
    'event_type_id' => 5,
    'venue_id' => 14,
    'title' => 'Meneer Anansi',
    'description' => 'Family storytelling session.',
    'event_day' => 'saturday',
    'start_time' => '15:00',
    'end_time' => '16:00',
],
[
    'event_type_id' => 5,
    'venue_id' => 11,
    'title' => 'Corrie voor kinderen',
    'description' => 'Kids storytelling.',
    'event_day' => 'saturday',
    'start_time' => '14:00',
    'end_time' => '15:15',
],
[
    'event_type_id' => 5,
    'venue_id' => 13,
    'title' => 'The history of the Ten Boom Family',
    'description' => 'Historical storytelling.',
    'event_day' => 'saturday',
    'start_time' => '13:00',
    'end_time' => '14:30',
],

[
    'event_type_id' => 5,
    'venue_id' => 14,
    'title' => 'Mister Anansi',
    'description' => 'Family storytelling.',
    'event_day' => 'sunday',
    'start_time' => '10:00',
    'end_time' => '11:00',
],
[
    'event_type_id' => 5,
    'venue_id' => 14,
    'title' => 'Meneer Anansi',
    'description' => 'Family storytelling.',
    'event_day' => 'sunday',
    'start_time' => '15:00',
    'end_time' => '16:00',
],
[
    'event_type_id' => 5,
    'venue_id' => 13,
    'title' => 'De geschiedenis van familie ten Boom',
    'description' => 'Ten Boom story.',
    'event_day' => 'sunday',
    'start_time' => '13:00',
    'end_time' => '14:30',
],
[
    'event_type_id' => 5,
    'venue_id' => 10,
    'title' => 'Winners of story telling competition, soties for Haarlem',
    'description' => 'Storytelling competition.',
    'event_day' => 'sunday',
    'start_time' => '16:00',
    'end_time' => '17:30',
],
            ['event_type_id' => 5, 'venue_id' => 1, 'title' => 'Mister Anansi', 'description' => 'Story session.', 'event_day' => 'saturday', 'start_time' => '13:00'],
            ['event_type_id' => 5, 'venue_id' => 1, 'title' => 'Meneer Anansi', 'description' => 'Story session.', 'event_day' => 'saturday', 'start_time' => '13:30'],
            ['event_type_id' => 5, 'venue_id' => 1, 'title' => 'Mister Anansi', 'description' => 'Story session.', 'event_day' => 'sunday', 'start_time' => '16:00'],
            ['event_type_id' => 5, 'venue_id' => 1, 'title' => 'Meneer Anansi', 'description' => 'Story session.', 'event_day' => 'sunday', 'start_time' => '16:30'],
        ])->saveData();
    }
}
