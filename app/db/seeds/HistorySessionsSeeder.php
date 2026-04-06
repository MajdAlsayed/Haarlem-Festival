<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistorySessionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        // Clear existing data to avoid duplicates
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->execute('DELETE FROM history_tours');
        $this->execute("DELETE FROM ticket_details WHERE category = 'history' AND ticket_type = 'event_ticket'");
        $this->execute('DELETE FROM sessions WHERE event_id IN (
    SELECT event_id FROM events WHERE event_type_id = 3
)');
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
        $days = [
            'thursday' => '2026-07-23',
            'friday' => '2026-07-24',
            'saturday' => '2026-07-25',
            'sunday' => '2026-07-26',
        ];

        // 3 time slots per day
        $times = [
            ['start' => '10:00:00', 'end' => '12:30:00'],
            ['start' => '13:00:00', 'end' => '15:30:00'],
            ['start' => '16:00:00', 'end' => '18:30:00'],
        ];

        // Dynamically find event_id by type and day
        foreach ($days as $eventDay => $date) {
            $event = $this->fetchRow(
                "SELECT event_id, title, price 
                FROM events
                WHERE event_type_id = 3 AND event_day = '$eventDay'
                LIMIT 1"
            );

            if (!$event) {
                continue;
            }

            $eventId = (int)$event['event_id'];
            $eventTitle = (string) $event['title'];
            $price = isset($event['price']) && is_numeric($event['price'])
                ? (float) $event['price']
                : 17.50;

            foreach ($times as $time) {
                $this->table('sessions')->insert([
                    [
                        'event_id' => $eventId,
                        'tickets_available' => 36,
                        'start_time' => $date . ' ' . $time['start'],
                        'end_time' => $date . ' ' . $time['end'],
                    ]
                ])->saveData();

                $sessionId = $this->fetchRow('SELECT LAST_INSERT_ID() as id')['id'];

                // Create 3 tours per session — one per language (12 tickets each)
                $languages = [
                    1 => 'English',
                    2 => 'Dutch',
                    3 => 'Chinese',
                ];

                foreach ($languages as $languageId => $languageName) {

                    // Create tour
                    $this->table('history_tours')->insert([
                        [
                            'session_id' => $sessionId,
                            'language_id' => $languageId,
                            'tickets_available' => 12,
                            'ticket_details_id' => null,
                            'ticket_family_id' => null,
                        ]
                    ])->saveData();

                    $historyTourId = (int) $this->fetchRow('SELECT LAST_INSERT_ID() as id')['id'];

                    // Create regular ticket
                    $this->table('ticket_details')->insert([
                        [
                            'event_id' => $eventId,
                            'session_id' => $sessionId,
                            'ticket_type' => 'event_ticket',
                            'category' => 'history',
                            'pass_day' => null,
                            'pass_time' => null,
                            'schedule_display' => $languageName . ' - ' . ucfirst($eventDay) . ' ' . substr($time['start'], 0, 5),
                            'sort_order' => 0,
                            'is_free' => $price <= 0 ? 1 : 0,
                            'name' => $eventTitle . ' - ' . $languageName . ' Tour (Regular)',
                            'description' => ucfirst($eventDay) . ' ' . substr($time['start'], 0, 5),
                            'price' => number_format($price, 2, '.', ''),
                        ]
                    ])->saveData();

                    $regularId = (int) $this->fetchRow('SELECT LAST_INSERT_ID() as id')['id'];

                    // Create Family Ticket
                    $this->table('ticket_details')->insert([
                        [
                            'event_id' => $eventId,
                            'session_id' => $sessionId,
                            'ticket_type' => 'event_ticket',
                            'category' => 'history',
                            'pass_day' => null,
                            'pass_time' => null,
                            'schedule_display' => $languageName . ' - ' . ucfirst($eventDay) . ' ' . substr($time['start'], 0, 5),
                            'sort_order' => 1,
                            'is_free' => 0,
                            'name' => $eventTitle . ' - ' . $languageName . ' Tour (Family)',
                            'description' => 'Up to 4 people. ' . ucfirst($eventDay) . ' ' . substr($time['start'], 0, 5),
                            'price' => '60.00',
                        ]
                    ])->saveData();

                    $familyId = (int) $this->fetchRow('SELECT LAST_INSERT_ID() as id')['id'];

                    // 4. обновляем tour
                    $this->execute(
                        "UPDATE history_tours
                         SET ticket_details_id = {$regularId}, ticket_family_id = {$familyId}
                         WHERE history_tour_id = {$historyTourId}"
                    );
                }
            }
        }
    }
}
