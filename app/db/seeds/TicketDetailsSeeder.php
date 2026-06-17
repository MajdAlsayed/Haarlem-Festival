<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Catalog for /tickets: day / all-access passes for **jazz & dance only** + event_ticket per jazz/dance/history/stories event.
 * Safe to re-run: upserts passes by type/category/day/time; upserts event tickets by event_id.
 */
final class TicketDetailsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->pruneUnreferencedPassDuplicates();

        foreach ($this->passRows() as $pass) {
            $this->upsertPass($pass);
        }

        $rows = $this->fetchAll(
            "SELECT e.event_id, e.title, e.price, e.event_day, e.hall, v.name AS venue_name, LOWER(et.name) AS cat
             FROM events e
             JOIN event_types et ON et.event_type_id = e.event_type_id
             JOIN venues v ON v.venue_id = e.venue_id
             WHERE LOWER(et.name) IN ('jazz','dance','stories')"
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
                    'name' => $cat === 'dance' ? 'All-Access Day Pass' : 'Day Pass',
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
                'name' => $cat === 'dance' ? 'All-Access Festival Pass' : 'All-Access Pass',
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
                    ['description' => "Access to all DANCE! events on Friday\nIncludes evening club sessions\nBest option for multiple Friday events", 'pass_day' => 'friday', 'pass_time' => '20:00', 'schedule_display' => null, 'price' => '150.00', 'sort' => 1],
                    ['description' => "Access to all DANCE! events on Saturday\nIncludes Caprera Openluchttheater session\nBest option for multiple Saturday events", 'pass_day' => 'saturday', 'pass_time' => '14:00', 'schedule_display' => null, 'price' => '150.00', 'sort' => 2],
                    ['description' => "Access to all DANCE! events on Sunday\nIncludes daytime and evening sets\nBest option for multiple Sunday events", 'pass_day' => 'sunday', 'pass_time' => '14:00', 'schedule_display' => null, 'price' => '150.00', 'sort' => 3],
                ],
                [
                    'description' => "Access to all DANCE! events on Friday, Saturday & Sunday\nOne pass for the full festival weekend\nIncludes Caprera Openluchttheater sessions",
                    'pass_day' => null,
                    'pass_time' => null,
                    'schedule_display' => 'Friday, Saturday, Sunday',
                    'price' => '250.00',
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
            'stories' => 10.0,
            default => 20.0,
        };
    }

    /**
     * @param array<string,mixed> $pass
     */
    private function upsertPass(array $pass): void
    {
        $ticketType = (string) $pass['ticket_type'];
        $category = (string) $pass['category'];
        $passDay = $pass['pass_day'];
        $passTime = $pass['pass_time'];

        $existing = $this->findPassRow($ticketType, $category, $passDay, $passTime);
        if ($existing !== null) {
            $tid = (int) $existing['ticket_details_id'];
            $this->execute(
                "UPDATE ticket_details SET
                    event_id = NULL,
                    session_id = NULL,
                    ticket_type = '{$this->esc($ticketType)}',
                    category = '{$this->esc($category)}',
                    pass_day = " . $this->sqlNullableString($passDay) . ",
                    pass_time = " . $this->sqlNullableString($passTime) . ",
                    schedule_display = " . $this->sqlNullableString($pass['schedule_display'] ?? null) . ",
                    sort_order = " . (int) ($pass['sort_order'] ?? 0) . ",
                    is_free = " . (int) ($pass['is_free'] ?? 0) . ",
                    name = '{$this->esc((string) $pass['name'])}',
                    description = '{$this->esc((string) $pass['description'])}',
                    price = '{$this->esc((string) $pass['price'])}'
                 WHERE ticket_details_id = {$tid}"
            );

            return;
        }

        $this->table('ticket_details')->insert($pass)->saveData();
    }

    /**
     * @return array{ticket_details_id: int}|null
     */
    private function findPassRow(string $ticketType, string $category, mixed $passDay, mixed $passTime): ?array
    {
        $sql = "SELECT ticket_details_id
                FROM ticket_details
                WHERE ticket_type = '{$this->esc($ticketType)}'
                  AND category = '{$this->esc($category)}'
                  AND " . $this->sqlEqualsNullable('pass_day', $passDay) . "
                  AND " . $this->sqlEqualsNullable('pass_time', $passTime) . "
                ORDER BY ticket_details_id ASC
                LIMIT 1";

        $row = $this->fetchRow($sql);

        return $row !== false && $row !== null ? $row : null;
    }

    private function pruneUnreferencedPassDuplicates(): void
    {
        $groups = $this->fetchAll(
            "SELECT ticket_type, category, pass_day, pass_time, COUNT(*) AS c
             FROM ticket_details
             WHERE ticket_type IN ('day_pass', 'all_access_pass')
             GROUP BY ticket_type, category, pass_day, pass_time
             HAVING c > 1"
        );

        foreach ($groups as $group) {
            $rows = $this->fetchAll(
                "SELECT ticket_details_id
                 FROM ticket_details
                 WHERE ticket_type = '{$this->esc((string) $group['ticket_type'])}'
                   AND category = '{$this->esc((string) $group['category'])}'
                   AND " . $this->sqlEqualsNullable('pass_day', $group['pass_day']) . "
                   AND " . $this->sqlEqualsNullable('pass_time', $group['pass_time']) . "
                 ORDER BY ticket_details_id ASC"
            );

            $keepId = $this->choosePassRowToKeep($rows);
            foreach ($rows as $row) {
                $id = (int) $row['ticket_details_id'];
                if ($id === $keepId || $this->passRowIsReferenced($id)) {
                    continue;
                }

                $this->execute("DELETE FROM ticket_details WHERE ticket_details_id = {$id}");
            }
        }
    }

    /**
     * @param list<array{ticket_details_id: int|string}> $rows
     */
    private function choosePassRowToKeep(array $rows): int
    {
        foreach ($rows as $row) {
            $id = (int) $row['ticket_details_id'];
            if ($this->passRowIsReferenced($id)) {
                return $id;
            }
        }

        return (int) $rows[0]['ticket_details_id'];
    }

    private function passRowIsReferenced(int $ticketDetailsId): bool
    {
        $orderRef = $this->fetchRow(
            "SELECT order_item_id FROM order_items WHERE ticket_details_id = {$ticketDetailsId} LIMIT 1"
        );
        if ($orderRef !== false && $orderRef !== null) {
            return true;
        }

        $cartRef = $this->fetchRow(
            "SELECT cart_item_id FROM cart_items WHERE ticket_details_id = {$ticketDetailsId} LIMIT 1"
        );

        return $cartRef !== false && $cartRef !== null;
    }

    private function sqlEqualsNullable(string $column, mixed $value): string
    {
        if ($value === null || $value === '') {
            return "({$column} IS NULL OR {$column} = '')";
        }

        return "{$column} = '{$this->esc((string) $value)}'";
    }

    private function sqlNullableString(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'NULL';
        }

        return "'" . $this->esc((string) $value) . "'";
    }
}
