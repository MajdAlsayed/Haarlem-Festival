<?php

namespace App\Services;

use App\Repositories\StoriesRepository;

class StoriesService
{
    public function __construct(private StoriesRepository $repo) {}

    public function getStoriesHomeData(?string $day = null): array
    {
        $stories = $this->repo->getStories($day);

        return [
            'stories'  => $stories,
            'schedule' => $this->buildSchedule($stories),
        ];
    }

    public function getVenuePageData(string $slug, ?string $day = null): array
    {
        $venue = $this->repo->getVenueBySlug($slug);

        if (!$venue) {
            return [
                'venue' => null,
                'stories' => [],
                'allVenueStories' => [],
                'schedule' => ['NL' => [], 'ENG' => []],
            ];
        }

        $storiesFiltered = $this->repo->getStoriesByVenue((int)$venue['venue_id'], $day);

        $allStories = $this->repo->getStories('all');

        $explorePool = array_values(array_filter(
            $allStories,
            function (array $story) use ($venue): bool {
                $storyVenueId = (int)($story['venue_id'] ?? 0);
                $currentVenueId = (int)($venue['venue_id'] ?? 0);
                $storyId = (int)($story['story_id'] ?? 0);

                return $storyVenueId !== $currentVenueId && $storyId > 0;
            }
        ));

        shuffle($explorePool);
        $exploreMore = array_slice($explorePool, 0, 4);

        return [
            'venue' => $venue,
            'stories' => $storiesFiltered,
            'allVenueStories' => $exploreMore,
            'schedule' => $this->buildSchedule($storiesFiltered),
        ];
    }

    public function getStoryDetailData(int $storyId): array
    {
        $story = $this->repo->getStoryById($storyId);

        if (!$story) {
            return [
                'story' => null,
                'stories' => [],
                'schedule' => ['NL' => [], 'ENG' => []],
            ];
        }

        $more = [];
        if (!empty($story['venue_id'])) {
            $more = $this->repo->getStoriesByVenue((int)$story['venue_id'], 'all');

            $more = array_values(array_filter(
                $more,
                fn(array $item): bool => (int)($item['story_id'] ?? 0) !== (int)$storyId
            ));
        }

        return [
            'story' => $story,
            'stories' => $more,
            'schedule' => $this->buildSchedule($more),
        ];
    }

    private function buildSchedule(array $stories): array
    {
        $out = ['NL' => [], 'ENG' => []];

        foreach ($stories as $s) {
            $lang = strtoupper(trim((string)($s['language'] ?? '')));
            if ($lang !== 'NL' && $lang !== 'ENG') {
                continue;
            }

            $day = strtolower(trim((string)($s['event_day'] ?? '')));
            if ($day === '') {
                continue;
            }

            $start = trim((string)($s['start_time'] ?? ''));
            $end = trim((string)($s['end_time'] ?? ''));

            $time = $start;
            if ($start !== '' && $end !== '') {
                $time = $start . '-' . $end;
            }

            $out[$lang][$day][] = [
                'time' => $time,
                'title' => (string)($s['story_name'] ?? $s['name'] ?? $s['title'] ?? ''),
                'age' => (string)($s['age'] ?? ''),
            ];
        }

        return $out;
    }
}