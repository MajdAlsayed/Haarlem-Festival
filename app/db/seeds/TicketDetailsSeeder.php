<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Catalog for /tickets: day / all-access passes for **jazz & dance only** + event_ticket per jazz/dance/history/stories event.
 * Safe to re-run: refreshes passes; upserts event tickets by event_id.
 */
final class TicketDetailsSeeder extends AbstractSeed
{
    public function run(): void
    {
        try {
            $this->execute("DELETE FROM ticket_details WHERE ticket_type IN ('day_pass', 'all_access_pass')");
        } catch (\Throwable $e) {
            echo '[WARN] TicketDetailsSeeder: could not clear passes (orders may reference rows): ' . $e->getMessage() . "\n";
        }

        $passes = $this->passRows();
        if ($passes !== []) {
            $this->table('ticket_details')->insert($passes)->saveData();
        }

        $rows = $this->fetchAll(
            "SELECT e.event_id, e.title, e.price, e.event_day, e.hall, v.name AS venue_name, LOWER(et.name) AS cat
             FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             JOIN venues v ON v.venue_id = e.venue_id
             WHERE LOWER(et.name) IN ('jazz','dance','history','stories')"
        );

        foreach ($rows as $ev) {
            $eid = (int) $ev['event_id'];
            $cat = (string) $ev['cat'];
            $venue = (string) $ev['venue_name'];
            $hall = $ev['hall'] !== null && $ev['hall'] !== '' ? (string) $ev['hall'] : '';
            $subtitle = $hall !== '' ? $venue . ' — ' . $hall : $venue;

            $priceRaw = $ev['price'];
            $price = $this->defaultPriceForCategory($cat, $priceRaw, (string) ($ev['event_day'] ?? ''));

            $isFree = $price <= 0.0;

            $existing = $this->fetchRow("SELECT ticket_details_id FROM ticket_details WHERE event_id = {$eid} LIMIT 1");
            $payload = [
                'event_id' => $eid,
                'session_id' => null,
                'ticket_type' => 'event_ticket',
                'category' => $cat,
                'pass_day' => null,
                'pass_time' => null,
                'schedule_display' => null,
                'sort_order' => 0,
                'is_free' => $isFree ? 1 : 0,
                'name' => (string) $ev['title'],
                'description' => $subtitle,
                'price' => number_format($price, 2, '.', ''),
            ];

            if ($existing) {
                $tid = (int) $existing['ticket_details_id'];
                $this->execute(
                    "UPDATE ticket_details SET ticket_type='event_ticket', category='{$this->esc($cat)}',
                     is_free=" . ($isFree ? 1 : 0) . ", name='{$this->esc($payload['name'])}',
                     description='{$this->esc($subtitle)}', price={$payload['price']}
                     WHERE ticket_details_id={$tid}"
                );
            } else {
                $this->table('ticket_details')->insert($payload)->saveData();
            }
        }
    }

    private function esc(string $s): string
    {
        return addslashes($s);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function passRows(): array
    {
        $out = [];

        foreach (['jazz', 'dance'] as $cat) {
            [$dayPasses, $allAccess] = $this->passesForCategory($cat);
            foreach ($dayPasses as $d) {
                $out[] = [
                    'event_id' => null,
                    'session_id' => null,
                    'ticket_type' => 'day_pass',
                    'category' => $cat,
                    'pass_day' => $d['pass_day'],
                    'pass_time' => $d['pass_time'] ?? null,
                    'schedule_display' => $d['schedule_display'] ?? null,
                    'sort_order' => (int) $d['sort'],
                    'is_free' => 0,
                    'name' => 'Day Pass',
                    'description' => $d['description'],
                    'price' => $d['price'],
                ];
            }
            $out[] = [
                'event_id' => null,
                'session_id' => null,
                'ticket_type' => 'all_access_pass',
                'category' => $cat,
                'pass_day' => null,
                'pass_time' => null,
                'schedule_display' => $allAccess['schedule_display'],
                'sort_order' => 10,
                'is_free' => 0,
                'name' => 'All-Access Pass',
                'description' => $allAccess['description'],
                'price' => $allAccess['price'],
            ];
        }

        return $out;
    }

    /**
     * @return array{0: list<array<string,mixed>>, 1: array<string,mixed>}
     */
    private function passesForCategory(string $cat): array
    {
        return match ($cat) {
            'jazz' => [
                [
                    ['description' => 'All access for 1 day', 'pass_day' => 'thursday', 'pass_time' => null, 'schedule_display' => null, 'price' => '35.00', 'sort' => 1],
                    ['description' => 'All access for 1 day', 'pass_day' => 'friday', 'pass_time' => null, 'schedule_display' => null, 'price' => '35.00', 'sort' => 2],
                    ['description' => 'All access for 1 day', 'pass_day' => 'saturday', 'pass_time' => null, 'schedule_display' => null, 'price' => '35.00', 'sort' => 3],
                ],
                [
                    'description' => 'All access for all festival jazz days',
                    'pass_day' => null,
                    'pass_time' => null,
                    'schedule_display' => 'Thursday, Friday, Saturday',
                    'price' => '80.00',
                ],
            ],
            'dance' => [
                [
                    ['description' => 'All access only for the day', 'pass_day' => 'friday', 'pass_time' => '20:00', 'schedule_display' => null, 'price' => '125.00', 'sort' => 1],
                    ['description' => 'All access only for the day', 'pass_day' => 'saturday', 'pass_time' => '14:00', 'schedule_display' => null, 'price' => '125.00', 'sort' => 2],
                    ['description' => 'All access only for the day', 'pass_day' => 'sunday', 'pass_time' => '14:00', 'schedule_display' => null, 'price' => '125.00', 'sort' => 3],
                ],
                [
                    'description' => 'All access for all dance nights',
                    'pass_day' => null,
                    'pass_time' => null,
                    'schedule_display' => 'Friday, Saturday, Sunday',
                    'price' => '299.00',
                ],
            ],
        };
    }

    private function defaultPriceForCategory(string $cat, mixed $priceRaw, string $eventDay): float
    {
        unset($eventDay);
        if ($priceRaw !== null && $priceRaw !== '' && is_numeric($priceRaw)) {
            return (float) $priceRaw;
        }

        return match ($cat) {
            'dance' => 75.0,
            'jazz' => 15.0,
            'history' => 17.50,
            'stories' => 10.0,
            default => 20.0,
        };
    }
}
