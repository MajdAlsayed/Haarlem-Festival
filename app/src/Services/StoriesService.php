<?php

namespace App\Services;

use App\Contracts\IStoriesRepository;
use App\Contracts\ServiceInterface\StoriesServiceInterface;
use App\Exceptions\NotFoundException;

class StoriesService implements StoriesServiceInterface
{
    public function __construct(private IStoriesRepository $repo)
    {
    }


    public function getStoriesHomeData(?string $day = null): array
    {
        $stories = $this->repo->getStories($day);

        return [
            'stories'  => $stories,
            'schedule' => $this->buildSchedule($stories),
        ];
    }

    public function getStoryDetailData(int $storyId): array
    {
        $story = $this->repo->getStoryById($storyId);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        return [
            'story'      => $story,
            'detailPage' => $this->repo->getDetailPageByStoryId($storyId),
        ];
    }



    public function getAllStoriesForAdmin(): array
    {
        return $this->repo->getAllStoriesForAdmin();
    }

    public function getStoryForEdit(int $storyId): ?array
    {
        return $this->repo->getStoryById($storyId);
    }

    public function createStory(array $data): int
    {
        return $this->repo->createStory($data);
    }

    public function updateStory(int $storyId, array $data): bool
    {
        return $this->repo->updateStory($storyId, $data);
    }

    public function deleteStory(int $storyId): bool
    {
        return $this->repo->deleteStory($storyId);
    }

    public function hasDetailPage(int $storyId): bool
    {
        return $this->repo->hasDetailPage($storyId);
    }


    public function getDetailPageForCms(string $slug): array
    {
        $story = $this->repo->getStoryBySlug($slug);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        $detailPage = $this->repo->getDetailPageByStoryId((int)$story['story_id']);

        return [
            'story'      => $story,
            'detailPage' => $detailPage,
        ];
    }

    public function saveDetailPage(int $storyId, array $data): bool
    {
        $highlights = isset($data['highlights']) && is_array($data['highlights'])
            ? $data['highlights']
            : [];

        $gallery = isset($data['gallery']) && is_array($data['gallery'])
            ? $data['gallery']
            : [];

        $payload = [
            'hero_image'            => isset($data['hero_image'])            && $data['hero_image']            !== '' ? $data['hero_image']            : null,
            'hero_heading'          => isset($data['hero_heading'])          && $data['hero_heading']          !== '' ? $data['hero_heading']          : null,
            'hero_description'      => isset($data['hero_description'])      && $data['hero_description']      !== '' ? $data['hero_description']      : null,
            'article_title'         => isset($data['article_title'])         && $data['article_title']         !== '' ? $data['article_title']         : null,
            'article_image'         => isset($data['article_image'])         && $data['article_image']         !== '' ? $data['article_image']         : null,
            'article_image_caption' => isset($data['article_image_caption']) && $data['article_image_caption'] !== '' ? $data['article_image_caption'] : null,
            'article_paragraph_1'   => isset($data['article_paragraph_1'])   && $data['article_paragraph_1']   !== '' ? $data['article_paragraph_1']   : null,
            'article_paragraph_2'   => isset($data['article_paragraph_2'])   && $data['article_paragraph_2']   !== '' ? $data['article_paragraph_2']   : null,
            'article_paragraph_3'   => isset($data['article_paragraph_3'])   && $data['article_paragraph_3']   !== '' ? $data['article_paragraph_3']   : null,
            'highlights'            => json_encode($highlights, JSON_UNESCAPED_UNICODE),
            'gallery'               => json_encode($gallery,    JSON_UNESCAPED_UNICODE),
        ];

        return $this->repo->saveDetailPage($storyId, $payload);
    }


    private function buildSchedule(array $stories): array
    {
        $out = ['NL' => [], 'ENG' => []];

        foreach ($stories as $story) {
            
            $lang = strtoupper(trim((string)($story['language'] ?? '')));
            $day  = strtolower(trim((string)($story['event_day'] ?? '')));

           
            if ($lang === '' || $day === '') {
                continue;
            }

           
            if (!isset($out[$lang])) {
                $out[$lang] = [];
            }

            $start = isset($story['start_time']) ? trim((string)$story['start_time']) : '';
            $end   = isset($story['end_time'])   ? trim((string)$story['end_time'])   : '';

            $time = $start;
            if ($start !== '' && $end !== '') {
                $time = $start . '–' . $end;
            }

            $out[$lang][$day][] = [
                'time'  => $time,
                'title' => (string)($story['story_name'] ?? $story['name'] ?? $story['title'] ?? ''),
                'age'   => (string)($story['age'] ?? ''),
            ];
        }

        return $out;
    }
}