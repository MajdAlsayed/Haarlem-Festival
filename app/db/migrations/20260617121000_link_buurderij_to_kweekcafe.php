<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LinkBuurderijToKweekcafe extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "INSERT INTO venues (name, address, city, capacity)
             SELECT 'Kweekcafe', 'Kleverlaan 9', 'Haarlem', 200
             WHERE NOT EXISTS (
                SELECT 1 FROM venues WHERE name = 'Kweekcafe' AND city = 'Haarlem'
             )"
        );

        $this->execute(
            "UPDATE events
             SET venue_id = (
                SELECT venue_id FROM venues
                WHERE name = 'Kweekcafe' AND city = 'Haarlem'
                LIMIT 1
             )
             WHERE title = 'The Story of Buurderij Haarlem'"
        );

        $this->execute(
            "UPDATE stories
             SET venue_id = (
                SELECT venue_id FROM venues
                WHERE name = 'Kweekcafe' AND city = 'Haarlem'
                LIMIT 1
             )
             WHERE slug = 'the-story-of-buurderij-haarlem'"
        );
    }

    public function down(): void
    {
        // Data migration only. Do not delete the venue because other rows may use it later.
    }
}
