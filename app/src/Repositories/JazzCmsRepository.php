<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Database writes for the jazz admin screens — the counterpart to JazzRepository (which only reads).
 *
 * Inserts/updates/deletes jazz events, optional preview audio, discography tracks, and band member rows.
 * Everything is scoped to the jazz event type so we don’t accidentally touch dance or history rows.
 */
final class JazzCmsRepository
{
    /** Shared PDO handle for this request. */
    private function db(): PDO
    {
        return Database::getConnection();
    }

    /** Primary key for the “jazz” row in `event_types` — every create uses this. */
    public function getJazzEventTypeId(): int
    {
        $stmt = $this->db()->query("SELECT event_type_id FROM event_types WHERE LOWER(name) = 'jazz' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new \RuntimeException('Jazz event type is missing. Run JazzSeeder or migrations.');
        }

        return (int) $row['event_type_id'];
    }

    /**
     * Venues dropdown for event create/edit.
     *
     * @return list<array{venue_id:int,name:string,city:string}>
     */
    public function listVenues(): array
    {
        $stmt = $this->db()->query('SELECT venue_id, name, city FROM venues ORDER BY name ASC');
        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static fn (array $r): array => [
            'venue_id' => (int) $r['venue_id'],
            'name' => (string) $r['name'],
            'city' => (string) $r['city'],
        ], $rows);
    }

    /**
     * All jazz events for the admin table, with venue names, weekday order, then time.
     *
     * @return list<array<string,mixed>>
     */
    public function listJazzEventsForAdmin(): array
    {
        $jazzId = $this->getJazzEventTypeId();
        $sql = '
            SELECT e.event_id, e.event_type_id, e.venue_id, e.title, e.description, e.event_day,
                   e.start_time, e.end_time, e.hall, e.seats, e.price,
                   v.name AS venue_name, v.city AS venue_city
            FROM events e
            JOIN venues v ON v.venue_id = e.venue_id
            WHERE e.event_type_id = :tid
            ORDER BY
                CASE LOWER(COALESCE(e.event_day,\'\'))
                    WHEN \'thursday\' THEN 1 WHEN \'friday\' THEN 2 WHEN \'saturday\' THEN 3 WHEN \'sunday\' THEN 4 ELSE 9
                END,
                COALESCE(e.start_time,\'99:99\') ASC, e.event_id ASC
        ';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['tid' => $jazzId]);
        /** @var list<array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $r): array => $this->normalizeEventRow($r), $rows);
    }

    /**
     * Fetch one jazz event if it exists and still belongs to the jazz type.
     *
     * @return ?array<string,mixed>
     */
    public function getJazzEventById(int $eventId): ?array
    {
        if ($eventId <= 0) {
            return null;
        }
        $jazzId = $this->getJazzEventTypeId();
        $stmt = $this->db()->prepare(
            'SELECT e.event_id, e.event_type_id, e.venue_id, e.title, e.description, e.event_day,
                    e.start_time, e.end_time, e.hall, e.seats, e.price,
                    v.name AS venue_name, v.city AS venue_city
             FROM events e
             JOIN venues v ON v.venue_id = e.venue_id
             WHERE e.event_id = :id AND e.event_type_id = :tid
             LIMIT 1'
        );
        $stmt->execute(['id' => $eventId, 'tid' => $jazzId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            return null;
        }

        return $this->normalizeEventRow($r);
    }

    /**
     * Normalizes types/strings so the admin form always sees predictable keys.
     *
     * @return array<string,mixed>
     */
    private function normalizeEventRow(array $r): array
    {
        return [
            'event_id' => (int) $r['event_id'],
            'event_type_id' => (int) $r['event_type_id'],
            'venue_id' => (int) $r['venue_id'],
            'title' => (string) $r['title'],
            'description' => $r['description'] !== null ? (string) $r['description'] : '',
            'event_day' => $r['event_day'] !== null ? strtolower(trim((string) $r['event_day'])) : '',
            'start_time' => $r['start_time'] !== null ? (string) $r['start_time'] : '',
            'end_time' => $r['end_time'] !== null ? (string) $r['end_time'] : '',
            'hall' => $r['hall'] !== null ? (string) $r['hall'] : '',
            'seats' => isset($r['seats']) && $r['seats'] !== null ? (int) $r['seats'] : null,
            'price' => isset($r['price']) && $r['price'] !== null ? (string) $r['price'] : null,
            'venue_name' => (string) ($r['venue_name'] ?? ''),
            'venue_city' => (string) ($r['venue_city'] ?? ''),
        ];
    }

    /** UPDATE path for the admin jazz event form. */
    public function updateJazzEvent(
        int $eventId,
        int $venueId,
        string $title,
        string $description,
        string $eventDay,
        string $startTime,
        ?string $endTime,
        ?string $hall,
        ?int $seats,
        ?string $price
    ): void {
        $this->assertJazzEvent($eventId);
        $stmt = $this->db()->prepare(
            'UPDATE events SET venue_id = :vid, title = :title, description = :desc, event_day = :day,
             start_time = :st, end_time = :et, hall = :hall, seats = :seats, price = :price
             WHERE event_id = :eid'
        );
        $stmt->execute([
            'vid' => $venueId,
            'title' => $title,
            'desc' => $description === '' ? null : $description,
            'day' => strtolower(trim($eventDay)),
            'st' => $startTime,
            'et' => $endTime === null || $endTime === '' ? null : $endTime,
            'hall' => $hall === null || $hall === '' ? null : $hall,
            'seats' => $seats,
            'price' => $price === null || $price === '' ? null : $price,
            'eid' => $eventId,
        ]);
    }

    /** INSERT path — returns the new `event_id`. */
    public function createJazzEvent(
        int $venueId,
        string $title,
        string $description,
        string $eventDay,
        string $startTime,
        ?string $endTime,
        ?string $hall,
        ?int $seats,
        ?string $price
    ): int {
        $tid = $this->getJazzEventTypeId();
        $stmt = $this->db()->prepare(
            'INSERT INTO events (event_type_id, venue_id, title, description, event_day, start_time, end_time, hall, seats, price)
             VALUES (:tid, :vid, :title, :desc, :day, :st, :et, :hall, :seats, :price)'
        );
        $stmt->execute([
            'tid' => $tid,
            'vid' => $venueId,
            'title' => $title,
            'desc' => $description === '' ? null : $description,
            'day' => strtolower(trim($eventDay)),
            'st' => $startTime,
            'et' => $endTime === null || $endTime === '' ? null : $endTime,
            'hall' => $hall === null || $hall === '' ? null : $hall,
            'seats' => $seats,
            'price' => $price === null || $price === '' ? null : $price,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    /** Hard delete — only allowed after assertJazzEvent passes. */
    public function deleteJazzEvent(int $eventId): void
    {
        $this->assertJazzEvent($eventId);
        $stmt = $this->db()->prepare('DELETE FROM events WHERE event_id = :id');
        $stmt->execute(['id' => $eventId]);
    }

    /** Ensures we’re not touching another genre’s row by mistake. */
    private function assertJazzEvent(int $eventId): void
    {
        if ($this->getJazzEventById($eventId) === null) {
            throw new \InvalidArgumentException('Jazz event not found.');
        }
    }

    /**
     * Optional teaser clip linked to this event (`event_audio`).
     *
     * @return ?array{audio_id:int,event_id:int,file_path:string,track_title:?string}
     */
    public function getEventAudio(int $eventId): ?array
    {
        try {
            $stmt = $this->db()->prepare(
                'SELECT audio_id, event_id, file_path, track_title FROM event_audio WHERE event_id = :eid LIMIT 1'
            );
            $stmt->execute(['eid' => $eventId]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return null;
        }
        if (!$r) {
            return null;
        }

        return [
            'audio_id' => (int) $r['audio_id'],
            'event_id' => (int) $r['event_id'],
            'file_path' => (string) $r['file_path'],
            'track_title' => $r['track_title'] !== null ? (string) $r['track_title'] : null,
        ];
    }

    /** Insert or update the single preview-audio row for this event. */
    public function upsertEventAudio(int $eventId, string $filePath, ?string $trackTitle): void
    {
        $filePath = str_replace('\\', '/', trim($filePath));
        if ($filePath === '') {
            throw new \InvalidArgumentException('Audio file path is required.');
        }
        $existing = $this->getEventAudio($eventId);
        if ($existing) {
            $stmt = $this->db()->prepare(
                'UPDATE event_audio SET file_path = :fp, track_title = :tt WHERE event_id = :eid'
            );
            $stmt->execute([
                'fp' => $filePath,
                'tt' => $trackTitle === '' ? null : $trackTitle,
                'eid' => $eventId,
            ]);
        } else {
            $stmt = $this->db()->prepare(
                'INSERT INTO event_audio (event_id, file_path, track_title) VALUES (:eid, :fp, :tt)'
            );
            $stmt->execute([
                'eid' => $eventId,
                'fp' => $filePath,
                'tt' => $trackTitle === '' ? null : $trackTitle,
            ]);
        }
    }

    /** Removes preview audio (ignored if the table is missing — older DBs). */
    public function deleteEventAudio(int $eventId): void
    {
        try {
            $stmt = $this->db()->prepare('DELETE FROM event_audio WHERE event_id = :eid');
            $stmt->execute(['eid' => $eventId]);
        } catch (\Throwable) {
            // table missing
        }
    }

    /**
     * Admin list: raw rows from `artist_discography` for one slug.
     *
     * @return list<array<string,mixed>>
     */
    public function listDiscographyBySlug(string $slug): array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return [];
        }
        try {
            $stmt = $this->db()->prepare(
                'SELECT track_id, artist_slug, title, release_year, duration_seconds, play_count, image_file, audio_file, sort_order
                 FROM artist_discography WHERE artist_slug = :s ORDER BY sort_order ASC, track_id ASC'
            );
            $stmt->execute(['s' => $slug]);
            /** @var list<array<string,mixed>> $rows */
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }

        return $rows;
    }

    /**
     * Single track for the edit screen.
     *
     * @return ?array<string,mixed>
     */
    public function getDiscographyTrack(int $trackId): ?array
    {
        if ($trackId <= 0) {
            return null;
        }
        try {
            $stmt = $this->db()->prepare(
                'SELECT track_id, artist_slug, title, release_year, duration_seconds, play_count, image_file, audio_file, sort_order
                 FROM artist_discography WHERE track_id = :id LIMIT 1'
            );
            $stmt->execute(['id' => $trackId]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return null;
        }

        return $r ?: null;
    }

    /** New album row — returns `track_id`. */
    public function insertDiscographyTrack(
        string $artistSlug,
        string $title,
        ?int $releaseYear,
        ?int $durationSeconds,
        int $playCount,
        string $imageFile,
        string $audioFile,
        int $sortOrder
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO artist_discography (artist_slug, title, release_year, duration_seconds, play_count, image_file, audio_file, sort_order)
             VALUES (:slug, :title, :ry, :dur, :pc, :img, :aud, :so)'
        );
        $stmt->execute([
            'slug' => strtolower(trim($artistSlug)),
            'title' => $title,
            'ry' => $releaseYear,
            'dur' => $durationSeconds,
            'pc' => $playCount,
            'img' => str_replace('\\', '/', trim($imageFile)),
            'aud' => str_replace('\\', '/', trim($audioFile)),
            'so' => $sortOrder,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    /** Overwrites metadata/paths for an existing track. */
    public function updateDiscographyTrack(
        int $trackId,
        string $artistSlug,
        string $title,
        ?int $releaseYear,
        ?int $durationSeconds,
        int $playCount,
        string $imageFile,
        string $audioFile,
        int $sortOrder
    ): void {
        $stmt = $this->db()->prepare(
            'UPDATE artist_discography SET artist_slug = :slug, title = :title, release_year = :ry, duration_seconds = :dur,
             play_count = :pc, image_file = :img, audio_file = :aud, sort_order = :so WHERE track_id = :id'
        );
        $stmt->execute([
            'slug' => strtolower(trim($artistSlug)),
            'title' => $title,
            'ry' => $releaseYear,
            'dur' => $durationSeconds,
            'pc' => $playCount,
            'img' => str_replace('\\', '/', trim($imageFile)),
            'aud' => str_replace('\\', '/', trim($audioFile)),
            'so' => $sortOrder,
            'id' => $trackId,
        ]);
    }

    /** Deletes one discography row by id. */
    public function deleteDiscographyTrack(int $trackId): void
    {
        $stmt = $this->db()->prepare('DELETE FROM artist_discography WHERE track_id = :id');
        $stmt->execute(['id' => $trackId]);
    }

    /**
     * Which artist slugs already have tracks (for admin tabs).
     *
     * @return list<string>
     */
    public function listDiscographySlugs(): array
    {
        try {
            $stmt = $this->db()->query(
                'SELECT DISTINCT artist_slug FROM artist_discography ORDER BY artist_slug ASC'
            );
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return array_map('strval', $rows);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Band admin table for one artist slug.
     *
     * @return list<array<string,mixed>>
     */
    public function listBandMembersBySlug(string $slug): array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return [];
        }
        try {
            $stmt = $this->db()->prepare(
                'SELECT member_id, artist_slug, name, role, image_file, sort_order
                 FROM jazz_band_members WHERE artist_slug = :s ORDER BY sort_order ASC, member_id ASC'
            );
            $stmt->execute(['s' => $slug]);
            /** @var list<array<string,mixed>> $rows */
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }

        return $rows;
    }

    /**
     * One band member row for editing.
     *
     * @return ?array<string,mixed>
     */
    public function getBandMember(int $memberId): ?array
    {
        if ($memberId <= 0) {
            return null;
        }
        try {
            $stmt = $this->db()->prepare(
                'SELECT member_id, artist_slug, name, role, image_file, sort_order
                 FROM jazz_band_members WHERE member_id = :id LIMIT 1'
            );
            $stmt->execute(['id' => $memberId]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return null;
        }

        return $r ?: null;
    }

    /** New person on the lineup — returns `member_id`. */
    public function insertBandMember(
        string $artistSlug,
        string $name,
        string $role,
        string $imageFile,
        int $sortOrder
    ): int {
        $stmt = $this->db()->prepare(
            'INSERT INTO jazz_band_members (artist_slug, name, role, image_file, sort_order)
             VALUES (:slug, :name, :role, :img, :so)'
        );
        $stmt->execute([
            'slug' => strtolower(trim($artistSlug)),
            'name' => $name,
            'role' => $role,
            'img' => str_replace('\\', '/', trim($imageFile)),
            'so' => $sortOrder,
        ]);

        return (int) $this->db()->lastInsertId();
    }

    /** Update name/role/photo/order for someone already in the table. */
    public function updateBandMember(
        int $memberId,
        string $artistSlug,
        string $name,
        string $role,
        string $imageFile,
        int $sortOrder
    ): void {
        $stmt = $this->db()->prepare(
            'UPDATE jazz_band_members SET artist_slug = :slug, name = :name, role = :role,
             image_file = :img, sort_order = :so WHERE member_id = :id'
        );
        $stmt->execute([
            'slug' => strtolower(trim($artistSlug)),
            'name' => $name,
            'role' => $role,
            'img' => str_replace('\\', '/', trim($imageFile)),
            'so' => $sortOrder,
            'id' => $memberId,
        ]);
    }

    /** Remove one band member row. */
    public function deleteBandMember(int $memberId): void
    {
        $stmt = $this->db()->prepare('DELETE FROM jazz_band_members WHERE member_id = :id');
        $stmt->execute(['id' => $memberId]);
    }

    /**
     * Which slugs already have band rows (for admin tabs).
     *
     * @return list<string>
     */
    public function listBandMemberSlugs(): array
    {
        try {
            $stmt = $this->db()->query(
                'SELECT DISTINCT artist_slug FROM jazz_band_members ORDER BY artist_slug ASC'
            );
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return array_map('strval', $rows);
        } catch (\Throwable) {
            return [];
        }
    }
}
