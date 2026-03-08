<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class StoriesSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['EventSeeder'];
    }

    public function run(): void
    {
        // Load Story events (event_type_id = 5)
        $events = $this->fetchAll("
            SELECT event_id, venue_id, title, event_day
            FROM events
            WHERE event_type_id = 5
        ");

        $norm = function (string $s): string {
            $s = mb_strtolower(trim($s));
            $s = preg_replace('/\s+/', ' ', $s);
            return $s;
        };

        $normDay = function (?string $s): string {
            return strtolower(trim((string)$s));
        };

        // Build maps:
        // 1) title only
        // 2) title + day
        // 3) count titles, so we can detect duplicate titles on multiple days
        $eventByTitle = [];
        $eventByTitleDay = [];
        $titleCounts = [];

        foreach ($events as $e) {
            $titleKey = $norm((string)$e['title']);
            $dayKey   = $normDay((string)($e['event_day'] ?? ''));
            $fullKey  = $titleKey . '|' . $dayKey;

            $eventByTitle[$titleKey] = $e; // last one wins, only used when unique
            $eventByTitleDay[$fullKey] = $e;
            $titleCounts[$titleKey] = ($titleCounts[$titleKey] ?? 0) + 1;
        }

        // Your stories data
        // If the same event title exists on multiple days in events table,
        // add 'event_day' => 'friday' / 'saturday' / 'sunday' to that row.
        $rows = [
            [
                'event_title' => 'Omdenken Podcast',
                'name' => 'Omdenken Podcast',
                'slug' => 'omdenken-podcast',
                'description' => "A funny and smart talk about thinking differently.\nLive recording with audience.",
                'image_path' => '/images/Stories/details/omdenken-main.jpg',
                'story_type' => 'Podcast',
                'age' => '16+',
                'language' => 'NL',
            ],
            [
                'event_title' => 'The Story of Buurderij Haarlem',
                'name' => 'The Story of Buurderij Haarlem',
                'slug' => 'the-story-of-buurderij-haarlem',
                'description' => "Local community story.\nHow Buurderij connects people with food and farmers.",
                'image_path' => '/images/Stories/cards/card-image-3.jpg',
                'story_type' => 'Story',
                'age' => '10+',
                'language' => 'ENG',
            ],
            [
                'event_title' => 'Corrie voor kinderen',
                'name' => 'Corrie voor kinderen',
                'slug' => 'corrie-voor-kinderen',
                'description' => "A kids storytelling performance.\nFun, easy, family-friendly.",
                'image_path' => '/images/Stories/cards/card-image-1.jpg',
                'story_type' => 'Kids',
                'age' => '6+',
                'language' => 'NL',
            ],
            [
                'event_title' => 'Winnie de Poeh',
                'name' => 'Winnie de Poeh',
                'slug' => 'winnie-de-poeh',
                'description' => "Warm and playful storytelling session.\nA story that brings a beloved children's book to life.",
                'image_path' => '/images/Stories/cards/card-image-2.jpg',
                'story_type' => 'Story',
                'age' => '4+',
                'language' => 'NL',
            ],
            [
                'event_title' => 'Flip Thinking Podcast',
                'name' => 'Flip Thinking Podcast',
                'slug' => 'flip-thinking-podcast',
                'description' => "A local story about sustainable food, community, and how Haarlem connects.",
                'image_path' => '/images/Stories/venues/link-1.jpg',
                'story_type' => 'Podcast',
                'age' => '16+',
                'language' => 'ENG',
            ],
            [
                'event_title' => 'Het verhaal van de Oeserzwammerij',
                'name' => 'Het verhaal van de Oeserzwammerij',
                'slug' => 'het-verhaal-van-de-oeserzwammerij',
                'description' => "A local story about sustainable food, community, and how Haarlem connects.",
                'image_path' => '/images/Stories/cards/card-image-8.jpg',
                'story_type' => 'Story',
                'age' => '12+',
                'language' => 'NL',
            ],
            [
                'event_title' => 'Winnaars van verhalenvertel wedstrijd, verhalen voor Haarlem',
                'name' => 'Winnaars van verhalenvertel wedstrijd, verhalen voor Haarlem',
                'slug' => 'winnaars-van-verhalenvertel-wedstrijd-verhalen-voor-haarlem',
                'description' => "A warm and playful storytelling session that brings a beloved children's story to life.",
                'image_path' => '/images/Stories/cards/card-image-7.jpg',
                'story_type' => 'Competition',
                'age' => '12+',
                'language' => 'NL',
            ],
            [
                'event_title' => 'Podcastlast Haarlem Special',
                'name' => 'Podcastlast Haarlem Special',
                'slug' => 'podcastlast-haarlem-special',
                'description' => "A special live recording where local voices and stories come together on stage.",
                'image_path' => '/images/Stories/cards/card-image-8.jpg',
                'story_type' => 'Podcast',
                'age' => '12+',
                'language' => 'NL',
            ],

            [
                'event_title' => 'De geschiedenis van familie ten Boom',
                'name' => 'De geschiedenis van familie ten Boom',
                'slug' => 'de-geschiedenis-van-familie-ten-boom',
                'description' => "A child-friendly story introducing the life and values of Corrie ten Boom.",
                'image_path' => '/images/Stories/cards/card-image-10.jpg',
                'story_type' => 'History',
                'age' => '12+',
                'language' => 'NL',
            ],

[
    'event_title' => 'Winners of story telling competition, soties for Haarlem',
    'name' => 'Winners of story telling competition, soties for Haarlem',
    'event_day' => 'sunday',
    'slug' => 'winners-of-story-telling-competition-soties-for-haarlem-sunday',
    'description' => "A warm and playful storytelling session that brings a beloved children's story to life.",
    'image_path' => '/images/Stories/cards/card-image-1.jpg',
    'story_type' => 'Competition',
    'age' => '12+',
    'language' => 'ENG',
],
            [
                'event_title' => 'The history of the Ten Boom Family',
                'name' => 'The history of the Ten Boom Family',
                'slug' => 'the-history-of-the-ten-boom-family',
                'description' => "A child-friendly story introducing the life and values of Corrie ten Boom.",
                'image_path' => '/images/Stories/cards/card-image-8.jpg',
                'story_type' => 'History',
                'age' => '12+',
                'language' => 'ENG',
            ],
            [
    'event_title' => 'Mister Anansi',
    'event_day' => 'saturday',
    'name' => 'Mister Anansi',
    'slug' => 'mister-anansi-saturday',
    'description' => "A meaningful space where Haarlem's past is kept alive through stories.",
    'image_path' => '/images/Stories/cards/card-image-10.jpg',
    'story_type' => 'Story',
    'age' => '2+',
    'language' => 'ENG',
],
[
    'event_title' => 'Meneer Anansi',
    'event_day' => 'saturday',
    'name' => 'Meneer Anansi',
    'slug' => 'meneer-anansi-saturday',
    'description' => "A meaningful space where Haarlem's past is kept alive through stories.",
    'image_path' => '/images/Stories/cards/card-image-9.jpg',
    'story_type' => 'Story',
    'age' => '2+',
    'language' => 'NL',
],
[
    'event_title' => 'Mister Anansi',
    'event_day' => 'sunday',
    'name' => 'Mister Anansi ',
    'slug' => 'mister-anansi-sunday',
    'description' => "A meaningful story session where Haarlem's past is brought to life in an engaging way.",
    'image_path' => '/images/Stories/cards/card-image-2.jpg',
    'story_type' => 'Story',
    'age' => '2+',
    'language' => 'ENG',
],
[
    'event_title' => 'Meneer Anansi',
    'event_day' => 'sunday',
    'name' => 'Meneer Anansi',
    'slug' => 'meneer-anansi-sunday',
    'description' => "A meaningful story session where Haarlem's past is brought to life in an engaging way.",
    'image_path' => '/images/Stories/cards/card-image-9.jpg',
    'story_type' => 'Story',
    'age' => '2+',
    'language' => 'NL',
],

        ];

        // Clean stories table
        $this->execute("DELETE FROM stories");

        $pdo = $this->getAdapter()->getConnection();
        $inserted = 0;

        // Check duplicate slugs inside the rows array before DB insert
        $seenSlugs = [];

        foreach ($rows as $r) {
            $slug = trim((string)$r['slug']);

            if (isset($seenSlugs[$slug])) {
                echo "[SKIP] Duplicate slug in seeder rows: {$slug}\n";
                continue;
            }

            $seenSlugs[$slug] = true;

            $titleKey = $norm((string)$r['event_title']);
            $rowDay   = $normDay($r['event_day'] ?? '');
            $fullKey  = $titleKey . '|' . $rowDay;

            // If same title exists multiple times in events table, require event_day in seeder row
            if (($titleCounts[$titleKey] ?? 0) > 1 && $rowDay === '') {
                echo "[SKIP] Ambiguous title, add event_day in seeder row: {$r['event_title']}\n";
                continue;
            }

            if ($rowDay !== '') {
                if (!isset($eventByTitleDay[$fullKey])) {
                    echo "[SKIP] Not found in events (title/day mismatch): {$r['event_title']} | {$rowDay}\n";
                    continue;
                }
                $event = $eventByTitleDay[$fullKey];
            } else {
                if (!isset($eventByTitle[$titleKey])) {
                    echo "[SKIP] Not found in events (title mismatch): {$r['event_title']}\n";
                    continue;
                }
                $event = $eventByTitle[$titleKey];
            }

            $eventId = (int)$event['event_id'];
            $venueId = (int)$event['venue_id'];

            $this->execute("
                INSERT INTO stories (name, slug, description, image_path, story_type, age, language, venue_id, event_id)
                VALUES (
                    " . $pdo->quote(trim((string)$r['name'])) . ",
                    " . $pdo->quote($slug) . ",
                    " . $pdo->quote((string)$r['description']) . ",
                    " . $pdo->quote((string)$r['image_path']) . ",
                    " . $pdo->quote((string)$r['story_type']) . ",
                    " . $pdo->quote((string)$r['age']) . ",
                    " . $pdo->quote((string)$r['language']) . ",
                    {$venueId},
                    {$eventId}
                )
            ");

            $inserted++;
        }

        echo "[DONE] Inserted stories: {$inserted}\n";
    }
}