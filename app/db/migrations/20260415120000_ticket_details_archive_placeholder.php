<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Internal ticket_details row used when an admin deletes a catalog ticket that still
 * appears on historical order lines (order_items FK would otherwise block DELETE).
 */
final class TicketDetailsArchivePlaceholder extends AbstractMigration
{
    private const NAME = '[SYSTEM] Archived catalog item';

    private const CATEGORY = 'internal';

    public function up(): void
    {
        $row = $this->fetchRow(
            "SELECT ticket_details_id FROM ticket_details WHERE name = '" . self::NAME . "' LIMIT 1"
        );
        if ($row !== null) {
            return;
        }

        $this->execute(
            "INSERT INTO ticket_details (event_id, session_id, ticket_type, category, pass_day, pass_time,
             schedule_display, sort_order, is_free, name, description, price)
             VALUES (NULL, NULL, 'event_ticket', '" . self::CATEGORY . "', NULL, NULL, NULL, 9999, 1,
             '" . self::NAME . "',
             'Placeholder for historical order lines after a ticket was removed from the catalog by an admin.',
             '0.00')"
        );
    }

    public function down(): void
    {
        $this->execute(
            "DELETE FROM ticket_details WHERE name = '" . self::NAME . "' AND LOWER(TRIM(category)) = '" . self::CATEGORY . "' LIMIT 1"
        );
    }
}
