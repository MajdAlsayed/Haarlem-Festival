<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class HistoryEventSeeder extends AbstractSeed
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
        // Use history event from EventSeeder
        $eventId = 4;

        // Create sessions ant tours
        $dates = ['2026-07-26', '2026-07-27', '2026-07-28', '2026-07-29'];
        $times = [
            ['start' => '10:00:00', 'end' => '12:30:00'],
            ['start' => '13:00:00', 'end' => '15:30:00'],
            ['start' => '16:00:00', 'end' => '18:30:00']
        ];

        // Sessions
        foreach ($dates as $date) {
            foreach ($times as $time) {
                $this->table('sessions')->insert([
                    [
                        'event_id' => $eventId,
                        'tickets_available' => 12,
                        'start_time' => $date . ' ' . $time['start'],
                        'end_time' => $date . ' ' . $time['end']
                    ]
                ])->saveData();
            }
        }

        $sessionId = $this->adapter->getConnection()->lastInsertId();

        // Tours
        $this->table('history_tours')->insert([
            ['session_id' => $sessionId, 'language_id' => 1, 'tickets_available' => 12],
            ['session_id' => $sessionId, 'language_id' => 2, 'tickets_available' => 12],
            ['session_id' => $sessionId, 'language_id' => 3, 'tickets_available' => 12]
        ])->saveData();
    }
}
