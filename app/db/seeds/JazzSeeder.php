<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class JazzSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 1) Ensure Jazz event type exists
        $eventType = $this->fetchRow("SELECT event_type_id FROM event_types WHERE LOWER(name)='jazz' LIMIT 1");
        if (!$eventType) {
            $this->execute("INSERT INTO event_types (name, description, card_image, info_path) VALUES ('jazz','Jazz events','jazz.jpg','/jazz')");
            $eventType = $this->fetchRow("SELECT event_type_id FROM event_types WHERE LOWER(name)='jazz' LIMIT 1");
        }
        $jazzTypeId = (int)$eventType['event_type_id'];

        // 2) Ensure required venues exist
        $this->ensureVenue('Patronaat', 'Haarlem');
        $this->ensureVenue('Grote Markt', 'Haarlem');

        $patronaatId = (int)$this->fetchRow("SELECT venue_id FROM venues WHERE name='Patronaat' LIMIT 1")['venue_id'];
        $groteMarktId = (int)$this->fetchRow("SELECT venue_id FROM venues WHERE name='Grote Markt' LIMIT 1")['venue_id'];

        // 3) Artists (only the 3 detail pages you have now)
        $this->upsertArtist('Gumbo Kings', 'gumbo-kings', 'Artist bio placeholder (store real bio here).', 'gumbo-kings.jpg', 1);
        $this->upsertArtist('Karsu', 'karsu', 'Artist bio placeholder (store real bio here).', 'karsu.jpg', 2);
        $this->upsertArtist('Gare du Nord', 'gare-du-nord', 'Artist bio placeholder (store real bio here).', 'gare-du-nord.jpg', 3);

        // 4) Jazz events (cards)
        $events = [
            // THURSDAY
            [$jazzTypeId,$patronaatId,'Gumbo Kings','Groove with the Gumbo Kings� soulful jazz!', 'thursday','18:00','19:00','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Evolve','Join Evolve for an unforgettable jazz experience!', 'thursday','19:30','20:30','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Ntjam Rosie','Experience Ntjam Rosie�s soulful performance!', 'thursday','21:00','22:00','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Wicked Jazz Sounds','Feel the groove with Wicked Jazz Sounds!', 'thursday','18:00','19:00','Second hall',10.00],
            [$jazzTypeId,$patronaatId,'Wouter Hamel','Enjoy Wouter Hamel�s captivating jazz tunes!', 'thursday','19:30','20:30','Second hall',10.00],
            [$jazzTypeId,$patronaatId,'Jonna Frazer','Feel the energy with Jonna Frazer live!', 'thursday','21:00','22:00','Second hall',10.00],

            // FRIDAY (one Karsu slot)
            [$jazzTypeId,$patronaatId,'Karsu','Experience Karsu\'s powerful voice and captivating melodies live.', 'friday','18:00','19:00','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Uncle Sue','Groove with Uncle Sue and their vibrant jazz tunes.', 'friday','19:30','20:30','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Chris Allen','Let Chris Allen mesmerize you with soulful rhythms.', 'friday','21:00','22:00','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Myles Sanko','Dive into Myles Sanko�s smooth jazz vibes.', 'friday','18:00','19:00','Second hall',10.00],
            [$jazzTypeId,$patronaatId,'Ilse Huizinga','Feel the elegance of Ilse Huizinga�s performance.', 'friday','19:30','20:30','Second hall',10.00],
            [$jazzTypeId,$patronaatId,'Eric Vloeimans en Hotspot','Enjoy Eric Vloeimans and Hotspot�s dynamic fusion.', 'friday','21:00','22:00','Second hall',10.00],

            // SATURDAY
            [$jazzTypeId,$patronaatId,'Gare du Nord','Immerse yourself in the smooth jazz stylings of Gare du Nord.', 'saturday','18:00','19:00','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Rilan & The Bombardiers','Electrifying fusion of jazz and soul.', 'saturday','19:30','20:30','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Soul Six','Soulful harmonies and dynamic sound.', 'saturday','21:00','22:00','Main hall',15.00],
            [$jazzTypeId,$patronaatId,'Han Bennink','Iconic jazz rhythms in an intimate setting.', 'saturday','18:00','19:00','Third hall',10.00],
            [$jazzTypeId,$patronaatId,'The Nordanians','Innovative and eclectic jazz sound.', 'saturday','19:30','20:30','Third hall',10.00],
            [$jazzTypeId,$patronaatId,'Lilith Merlot','Close the night with Lilith Merlot�s captivating performance.', 'saturday','21:00','22:00','Third hall',10.00],

            // SUNDAY (open air examples)
            [$jazzTypeId,$groteMarktId,'Ruis Soundsystem','Kick off Sunday with dynamic beats (free).', 'sunday','15:00','16:00',null,0.00],
            [$jazzTypeId,$groteMarktId,'Wicked Jazz Sounds','Smooth tunes on the square (free).', 'sunday','16:00','17:00',null,0.00],
            [$jazzTypeId,$groteMarktId,'Evolve','Signature sound outdoors (free).', 'sunday','17:00','18:00',null,0.00],
            [$jazzTypeId,$groteMarktId,'The Nordanians','Eclectic fusion outdoors (free).', 'sunday','18:00','19:00',null,0.00],
            [$jazzTypeId,$groteMarktId,'Gumbo Kings','Soulful performance outdoors (free).', 'sunday','19:00','20:00',null,0.00],
            [$jazzTypeId,$patronaatId,'Gare du Nord','Special Sunday indoor session.', 'sunday','18:00','19:00','Main hall',15.00],
        ];

        foreach ($events as $e) {
            // Rows may be [..., hall, price] (9 elems) or [..., hall, seats, price] (10 elems).
            $typeId   = $e[0];
            $venueId  = $e[1];
            $title    = $e[2];
            $desc     = $e[3];
            $day      = $e[4];
            $start    = $e[5];
            $end      = $e[6];
            $hall     = $e[7];
            if (count($e) >= 10) {
                $seats = $e[8] !== null && $e[8] !== '' ? (int) $e[8] : null;
                $price = $e[9];
            } else {
                $price = $e[8];
                // Paid shows in a hall get a default cap (was always NULL before: count($e) >= 10 was never true).
                $priceNum = is_numeric($price) ? (float) $price : 0.0;
                $seats = ($priceNum > 0.0 && $hall !== null && $hall !== '') ? 150 : null;
            }

            $titleEsc = addslashes($title);
            $descEsc  = addslashes($desc);
            $dayEsc   = addslashes($day);
            $startEsc = addslashes($start);
            $endEsc   = $end ? ("'".addslashes($end)."'") : "NULL";
            $hallEsc  = $hall ? ("'".addslashes($hall)."'") : "NULL";
            $seatsVal = $seats !== null ? (string)$seats : "NULL";
            $priceVal = is_numeric($price) ? (string)$price : "NULL";

            $this->execute("
                INSERT INTO events (event_type_id, venue_id, title, description, event_day, start_time, end_time, hall, seats, price)
                VALUES ($typeId, $venueId, '$titleEsc', '$descEsc', '$dayEsc', '$startEsc', $endEsc, $hallEsc, $seatsVal, $priceVal)
                ON DUPLICATE KEY UPDATE
                    description=VALUES(description),
                    venue_id=VALUES(venue_id),
                    end_time=VALUES(end_time),
                    hall=VALUES(hall),
                    seats=VALUES(seats),
                    price=VALUES(price)
            ");
        }

        // 5) Link events to artists for the 3 detail pages (optional but clean)
        $this->linkArtistToEventTitle('gumbo-kings', 'Gumbo Kings');
        $this->linkArtistToEventTitle('karsu', 'Karsu');
        $this->linkArtistToEventTitle('gare-du-nord', 'Gare du Nord');
    }

    private function ensureVenue(string $name, string $city): void
    {
        $row = $this->fetchRow("SELECT venue_id FROM venues WHERE name='".addslashes($name)."' LIMIT 1");
        if ($row) return;

        $this->execute("
            INSERT INTO venues (name, address, city, capacity)
            VALUES ('".addslashes($name)."', '', '".addslashes($city)."', 0)
        ");
    }

    private function upsertArtist(string $name, string $slug, string $bio, ?string $image, int $sort): void
    {
        $nameEsc = addslashes($name);
        $slugEsc = addslashes($slug);
        $bioEsc  = addslashes($bio);
        $imgEsc  = $image ? ("'".addslashes($image)."'") : "NULL";

        $this->execute("
            INSERT INTO artists (name, slug, bio, image_filename, sort_order)
            VALUES ('$nameEsc', '$slugEsc', '$bioEsc', $imgEsc, $sort)
            ON DUPLICATE KEY UPDATE
                name=VALUES(name),
                bio=VALUES(bio),
                image_filename=VALUES(image_filename),
                sort_order=VALUES(sort_order)
        ");
    }

    private function linkArtistToEventTitle(string $artistSlug, string $eventTitle): void
    {
        $artist = $this->fetchRow("SELECT id FROM artists WHERE slug='".addslashes($artistSlug)."' LIMIT 1");
        $event  = $this->fetchRow("SELECT event_id FROM events WHERE title='".addslashes($eventTitle)."' LIMIT 1");
        if (!$artist || !$event) return;

        $artistId = (int)$artist['id'];
        $eventId  = (int)$event['event_id'];

        $this->execute("
            INSERT IGNORE INTO event_artists (event_id, artist_id)
            VALUES ($eventId, $artistId)
        ");
    }
}