<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistorySessionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $days = [
            'thursday' => '2026-07-23',
            'friday'   => '2026-07-24',
            'saturday' => '2026-07-25',
            'sunday'   => '2026-07-26',
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
                "SELECT event_id FROM events WHERE event_type_id = 3 AND event_day = '$eventDay'"
            );

            if (!$event) {
                continue;
            }

            $eventId = $event['event_id'];

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
                $this->table('history_tours')->insert([
                    ['session_id' => $sessionId, 'language_id' => 1, 'tickets_available' => 12],
                    ['session_id' => $sessionId, 'language_id' => 2, 'tickets_available' => 12],
                    ['session_id' => $sessionId, 'language_id' => 3, 'tickets_available' => 12],
                ])->saveData();
            }
        }
    }
}
