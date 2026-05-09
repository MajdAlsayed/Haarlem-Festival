<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * All the SELECTs the jazz front-end needs — no writes here.
 *
 * We join events to venues and filter to the “jazz” type. There’s a little subquery that finds the matching
 * `ticket_details` row so artist pages can build “Add to program” forms that know which catalog id to post.
 * attachPreviewAudio() is a helper that turns file paths from `event_audio` into real /audio/… URLs.
 */
final class JazzRepository
{
    /**
     * @return array<int, array{
     *   event_id:int,
     *   title:string,
     *   description:?string,
     *   event_day:?string,
     *   start_time:?string,
     *   end_time:?string,
     *   hall:?string,
     *   seats:?int,
     *   price:?string,
     *   venue_name:string,
     *   venue_city:string,
     *   ticket_details_id:?int
     * }>
     *
     * Main query powering the /jazz grid — tries the “wide” SELECT first, falls back if older DBs lack columns.
     */
    public function getAll(): array
    {
        $db = Database::getConnection();

        $ticketSub = "(SELECT td.ticket_details_id FROM ticket_details td
            WHERE td.event_id = e.event_id AND td.ticket_type = 'event_ticket' LIMIT 1) AS ticket_details_id";

        $baseSql = "
            SELECT
                e.event_id,
                e.title,
                e.description,
                e.event_day,
                e.start_time,
                v.name AS venue_name,
                v.city AS venue_city,
                {$ticketSub}
            FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            JOIN venues v ON e.venue_id = v.venue_id
            WHERE LOWER(et.name) = 'jazz'
            ORDER BY
                CASE LOWER(COALESCE(e.event_day,'friday'))
                    WHEN 'thursday' THEN 1
                    WHEN 'friday' THEN 2
                    WHEN 'saturday' THEN 3
                    WHEN 'sunday' THEN 4
                    ELSE 9
                END,
                COALESCE(e.start_time,'99:99') ASC
        ";

        $fullSql = "
            SELECT
                e.event_id,
                e.title,
                e.description,
                e.event_day,
                e.start_time,
                e.end_time,
                e.hall,
                e.seats,
                e.price,
                v.name AS venue_name,
                v.city AS venue_city,
                {$ticketSub}
            FROM events e
            JOIN event_types et ON e.event_type_id = et.event_type_id
            JOIN venues v ON e.venue_id = v.venue_id
            WHERE LOWER(et.name) = 'jazz'
            ORDER BY
                CASE LOWER(COALESCE(e.event_day,'friday'))
                    WHEN 'thursday' THEN 1
                    WHEN 'friday' THEN 2
                    WHEN 'saturday' THEN 3
                    WHEN 'sunday' THEN 4
                    ELSE 9
                END,
                COALESCE(e.start_time,'99:99') ASC
        ";

        try {
            $stmt = $db->query($fullSql);
        } catch (\Throwable $e) {
            $stmt = $db->query($baseSql);
        }

        /** @var array<int, array<string,mixed>> $rows */
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($r) {
            $tid = $r['ticket_details_id'] ?? null;

            return [
                'event_id' => (int)$r['event_id'],
                'title' => (string)$r['title'],
                'description' => $r['description'] !== null ? (string)$r['description'] : null,
                'event_day' => $r['event_day'] !== null ? strtolower(trim((string)$r['event_day'])) : null,
                'start_time' => $r['start_time'] !== null ? (string)$r['start_time'] : null,
                'end_time' => isset($r['end_time']) && $r['end_time'] !== null ? (string)$r['end_time'] : null,
                'hall' => isset($r['hall']) && $r['hall'] !== null ? (string)$r['hall'] : null,
                'seats' => isset($r['seats']) && $r['seats'] !== null ? (int)$r['seats'] : null,
                'price' => isset($r['price']) && $r['price'] !== null ? (string)$r['price'] : null,
                'venue_name' => (string)$r['venue_name'],
                'venue_city' => (string)$r['venue_city'],
                'ticket_details_id' => $tid !== null && $tid !== '' ? (int) $tid : null,
            ];
        }, $rows);
    }

    /**
     * Filters {@see getAll()} to a single weekday string (thursday…sunday).
     *
     * @return array<int, array<string,mixed>>
     */
    public function getByDay(string $day): array
    {
        $day = strtolower(trim($day));
        $all = $this->getAll();
        return array_values(array_filter($all, fn($e) => ($e['event_day'] ?? '') === $day));
    }

    /**
     * All jazz rows whose title matches exactly (case-insensitive) — used to build one artist’s schedule.
     *
     * @return array<int, array<string,mixed>>
     */
    public function getByTitle(string $title): array
    {
        $title = strtolower(trim($title));
        $all = $this->getAll();

        return array_values(array_filter($all, function ($e) use ($title) {
            return strtolower(trim((string)$e['title'])) === $title;
        }));
    }

    /**
     * Enriches schedule rows with `preview_audio` URLs when `event_audio` has a clip (Gumbo / Gare pages).
     *
     * @param array<int, array<string,mixed>> $events
     * @return array<int, array<string,mixed>>
     */
    public function attachPreviewAudio(array $events): array
    {
        if ($events === []) {
            return $events;
        }
        $ids = array_values(array_unique(array_map(fn ($e) => (int) ($e['event_id'] ?? 0), $events)));
        $ids = array_values(array_filter($ids, fn ($id) => $id > 0));
        if ($ids === []) {
            return $events;
        }

        $db = Database::getConnection();
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare(
                "SELECT event_id, file_path, track_title FROM event_audio WHERE event_id IN ($placeholders)"
            );
            $stmt->execute($ids);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return $events;
        }

        $byEvent = [];
        foreach ($rows as $r) {
            $eid = (int) $r['event_id'];
            $path = (string) ($r['file_path'] ?? '');
            $byEvent[$eid] = [
                'url' => $this->buildAudioPublicUrl($path),
                'track_title' => isset($r['track_title']) && $r['track_title'] !== null ? (string) $r['track_title'] : null,
            ];
        }

        foreach ($events as $i => $e) {
            $eid = (int) ($e['event_id'] ?? 0);
            $events[$i]['preview_audio'] = $byEvent[$eid] ?? null;
        }

        return $events;
    }

    /** Turns a stored relative path into a browser-safe `/audio/...` URL with encoded segments. */
    private function buildAudioPublicUrl(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', trim($relativePath, '/'));
        if ($relativePath === '') {
            return '/audio/';
        }
        $segments = explode('/', $relativePath);

        return '/audio/' . implode('/', array_map('rawurlencode', $segments));
    }

    /**
     * Discography rows for a jazz artist page (e.g. Karsu). Empty if table missing or none.
     * Paths become absolute `image_url` / `audio_url` for the templates.
     *
     * @return list<array{
     *   track_id:int,
     *   title:string,
     *   release_year:?int,
     *   duration_seconds:?int,
     *   play_count:int,
     *   image_url:string,
     *   audio_url:string,
     *   sort_order:int
     * }>
     */
    public function getDiscographyByArtistSlug(string $slug): array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return [];
        }

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare(
                'SELECT track_id, title, release_year, duration_seconds, play_count, image_file, audio_file, sort_order
                 FROM artist_discography
                 WHERE artist_slug = :slug
                 ORDER BY sort_order ASC, track_id ASC'
            );
            $stmt->execute(['slug' => $slug]);
            /** @var array<int, array<string, mixed>> $rows */
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }

        return array_map(function (array $r): array {
            return [
                'track_id' => (int) $r['track_id'],
                'title' => (string) $r['title'],
                'release_year' => $r['release_year'] !== null ? (int) $r['release_year'] : null,
                'duration_seconds' => $r['duration_seconds'] !== null ? (int) $r['duration_seconds'] : null,
                'play_count' => (int) $r['play_count'],
                'image_url' => $this->buildJazzImagePublicUrl((string) $r['image_file']),
                'audio_url' => $this->buildAudioPublicUrl((string) $r['audio_file']),
                'sort_order' => (int) $r['sort_order'],
            ];
        }, $rows);
    }

    /**
     * Band members for an artist page (ordered). Empty if table missing or none.
     * Image paths are expanded to full `/images/jazz/...` URLs.
     *
     * @return list<array{name:string,role:string,img:string}>
     */
    public function getBandMembersByArtistSlug(string $slug): array
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            return [];
        }

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare(
                'SELECT name, role, image_file FROM jazz_band_members
                 WHERE artist_slug = :slug ORDER BY sort_order ASC, member_id ASC'
            );
            $stmt->execute(['slug' => $slug]);
            /** @var array<int, array<string, mixed>> $rows */
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable) {
            return [];
        }

        return array_map(function (array $r): array {
            return [
                'name' => (string) $r['name'],
                'role' => (string) $r['role'],
                'img' => $this->buildJazzImagePublicUrl((string) $r['image_file']),
            ];
        }, $rows);
    }

    /** File name or subpath under `public/images/jazz` → public URL. */
    private function buildJazzImagePublicUrl(string $fileName): string
    {
        $fileName = str_replace('\\', '/', trim($fileName, '/'));
        if ($fileName === '') {
            return '/images/jazz/';
        }
        if (str_contains($fileName, '/')) {
            $segments = explode('/', $fileName);

            return '/images/jazz/' . implode('/', array_map('rawurlencode', $segments));
        }

        return '/images/jazz/' . rawurlencode($fileName);
    }
}