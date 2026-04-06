<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Aligns dance day / festival passes and B2B2B standard ticket copy & prices with the event-detail / tickets UX.
 */
final class DancePassMarketingCopyAndPrices extends AbstractMigration
{
    public function up(): void
    {
        /** @var \PDO $pdo */
        $pdo = $this->getAdapter()->getConnection();

        $b2b2bDesc = "Access to Back2Back outdoor session\nHardwell, Martin Garrix, Armin van Buuren\nLong session (approx. 540 minutes)\nOpen-air venue experience\nLimited capacity";
        $st = $pdo->prepare(
            "UPDATE ticket_details td
             INNER JOIN events e ON e.event_id = td.event_id
             INNER JOIN event_types et ON et.event_type_id = e.event_type_id
             SET td.price = 110.00,
                 td.description = :desc
             WHERE td.ticket_type = 'event_ticket'
               AND LOWER(et.name) = 'dance'
               AND e.title = 'Hardwell / Garrix / Armin - B2B2B Outdoor Show'
               AND LOWER(COALESCE(e.event_day, '')) = 'saturday'
               AND e.start_time = '14:00'"
        );
        $st->execute(['desc' => $b2b2bDesc]);

        $daySpecs = [
            'friday' => [
                'desc' => "Access to all DANCE! events on Friday\nIncludes evening club sessions\nBest option for multiple Friday events",
            ],
            'saturday' => [
                'desc' => "Access to all DANCE! events on Saturday\nIncludes Caprera Openluchttheater session\nBest option for multiple Saturday events",
            ],
            'sunday' => [
                'desc' => "Access to all DANCE! events on Sunday\nIncludes daytime and evening sets\nBest option for multiple Sunday events",
            ],
        ];

        $updDay = $pdo->prepare(
            "UPDATE ticket_details
             SET name = 'All-Access Day Pass',
                 description = :desc,
                 price = 150.00
             WHERE ticket_type = 'day_pass'
               AND LOWER(category) = 'dance'
               AND LOWER(COALESCE(pass_day, '')) = :day"
        );

        foreach ($daySpecs as $day => $spec) {
            $updDay->execute(['desc' => $spec['desc'], 'day' => $day]);
        }

        $festDesc = "Access to all DANCE! events on Friday, Saturday & Sunday\nOne pass for the full festival weekend\nIncludes Caprera Openluchttheater sessions";
        $pdo->exec(
            "UPDATE ticket_details
             SET name = 'All-Access Festival Pass',
                 description = " . $pdo->quote($festDesc) . ",
                 price = 250.00,
                 schedule_display = 'Friday, Saturday, Sunday'
             WHERE ticket_type = 'all_access_pass'
               AND LOWER(category) = 'dance'"
        );
    }

    public function down(): void
    {
    }
}
