<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Ensures ticket_details rows for headline dance events if the DB was seeded without TicketDetailsSeeder:
 * B2B2B outdoor (Sat 14:00), Armin trance club (Sun 19:00), Hardwell final night (Sun 21:00).
 */
final class EnsureDanceHeadlineEventTickets extends AbstractMigration
{
    public function up(): void
    {
        /** @var \PDO $pdo */
        $pdo = $this->getAdapter()->getConnection();

        $specs = [
            ['title' => 'Hardwell / Garrix / Armin - B2B2B Outdoor Show', 'day' => 'saturday', 'time' => '14:00'],
            ['title' => 'Armin van Buuren – Trance Club Set', 'day' => 'sunday', 'time' => '19:00'],
            ['title' => 'Hardwell – Final Night Club Show', 'day' => 'sunday', 'time' => '21:00'],
        ];

        $find = $pdo->prepare(
            "SELECT e.event_id, e.title, v.name AS venue_name
             FROM events e
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             INNER JOIN venues v ON v.venue_id = e.venue_id
             WHERE LOWER(et.name) = 'dance'
               AND e.title = :title
               AND LOWER(COALESCE(e.event_day, '')) = :day
               AND e.start_time = :time
             LIMIT 1"
        );

        $hasTicket = $pdo->prepare(
            "SELECT ticket_details_id FROM ticket_details WHERE event_id = :eid AND ticket_type = 'event_ticket' LIMIT 1"
        );

        $insert = $pdo->prepare(
            "INSERT INTO ticket_details (event_id, session_id, ticket_type, category, pass_day, pass_time,
             schedule_display, sort_order, is_free, name, description, price)
             VALUES (:eid, NULL, 'event_ticket', 'dance', NULL, NULL, NULL, 0, 0, :name, :desc, :price)"
        );

        foreach ($specs as $spec) {
            $find->execute([
                'title' => $spec['title'],
                'day' => $spec['day'],
                'time' => $spec['time'],
            ]);
            $ev = $find->fetch(\PDO::FETCH_ASSOC);
            if (!$ev || !isset($ev['event_id'])) {
                continue;
            }
            $eid = (int) $ev['event_id'];
            $hasTicket->execute(['eid' => $eid]);
            if ($hasTicket->fetch(\PDO::FETCH_ASSOC)) {
                continue;
            }
            $venue = (string) ($ev['venue_name'] ?? '');
            $title = (string) $ev['title'];
            $subtitle = $venue !== '' ? $venue . ' — ' . $title : $title;
            $insert->execute([
                'eid' => $eid,
                'name' => $title,
                'desc' => $subtitle,
                'price' => '112.00',
            ]);
        }

        $this->execute(
            "UPDATE events e
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             SET e.seats = 150
             WHERE LOWER(et.name) = 'dance'
               AND e.seats IS NULL
               AND e.title IN (
                 'Hardwell / Garrix / Armin - B2B2B Outdoor Show',
                 'Armin van Buuren – Trance Club Set',
                 'Hardwell – Final Night Club Show'
               )"
        );
    }

    public function down(): void
    {
    }
}
