<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Links Gumbo Kings jazz events to preview MP3s in public/audio/Jazz audio/
 */
final class GumboKingsEventAudioSeeder extends AbstractSeed
{
    public function run(): void
    {
        if (!$this->hasTable('event_audio')) {
            echo "[SKIP] event_audio table missing — run migrations first\n";
            return;
        }

        // Thursday Patronaat — Iko Iko
        $this->execute("
            INSERT INTO event_audio (event_id, file_path, track_title)
            SELECT e.event_id,
                   'Jazz audio/Gumbo king Iko Iko - Dr. John.mp3',
                   'Iko Iko — Dr. John'
            FROM events e
            INNER JOIN event_types et ON e.event_type_id = et.event_type_id
            WHERE LOWER(et.name) = 'jazz'
              AND e.title = 'Gumbo Kings'
              AND LOWER(TRIM(COALESCE(e.event_day, ''))) = 'thursday'
            ORDER BY e.event_id ASC
            LIMIT 1
            ON DUPLICATE KEY UPDATE
                file_path = VALUES(file_path),
                track_title = VALUES(track_title)
        ");

        // Sunday Gumbo Kings (Grote Markt, 19:00) — Big Chief
        $this->execute("
            INSERT INTO event_audio (event_id, file_path, track_title)
            SELECT e.event_id,
                   'Jazz audio/Gumbo king Big Chief - Dr. John.mp3',
                   'Big Chief — Dr. John'
            FROM events e
            INNER JOIN event_types et ON e.event_type_id = et.event_type_id
            WHERE LOWER(et.name) = 'jazz'
              AND e.title = 'Gumbo Kings'
              AND LOWER(TRIM(COALESCE(e.event_day, ''))) = 'sunday'
              AND (e.start_time = '19:00' OR e.start_time = '19:00:00')
            ORDER BY e.event_id ASC
            LIMIT 1
            ON DUPLICATE KEY UPDATE
                file_path = VALUES(file_path),
                track_title = VALUES(track_title)
        ");

        echo "[DONE] GumboKingsEventAudioSeeder: linked preview audio to Gumbo Kings events\n";
    }
}
