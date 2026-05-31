<?php

namespace App\Controllers;

use App\Repositories\StoriesRepository;
use App\Services\StoriesService;
use App\ViewModels\StoriesViewModel;
use App\Exceptions\NotFoundException;

// Main controller for Stories
class StoriesController
{
    private const ALLOWED_DAYS = ['all', 'thursday', 'friday', 'saturday', 'sunday'];

    private StoriesService $storiesService;

    public function __construct()
    {
        // Using a Service to keep business logic separate
        $this->storiesService = new StoriesService(new StoriesRepository());
    }

    public function home(): void
    {
        // Get first 3 stories for featured cards on home page
        $allStories = $this->storiesService->getStoriesHomeData('all')['stories'] ?? [];
        $featured = array_slice($allStories, 0, 3);
        
        $data = [
            'featured' => $featured,
            'pageTitle' => 'Stories in Haarlem',
        ];
        $vm = new StoriesViewModel($data, 'all');
        require __DIR__ . '/../Views/Stories/Home.php';
    }

    public function events(): void
    {
        // Get the day filter from user
        $day  = $this->normalizeDay($_GET['day'] ?? 'all');
        
        // Fetch stories and prepare for display
        $data = $this->storiesService->getStoriesHomeData($day);
        $data['pageTitle'] = 'Stories in Haarlem';
        $vm = new StoriesViewModel($data, $day);
        require __DIR__ . '/../Views/Stories/Events.php';
    }

    public function detail(): void
    {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($id === false || $id === 0) {
            throw new NotFoundException('Invalid or missing story ID.');
        }

        $data = $this->storiesService->getStoryDetailData((int)$id);

        if (empty($data['story'])) {
            throw new NotFoundException('Story not found.');
        }

        $data['pageTitle'] = $data['story']['name'] ?? 'Story Details';
        $vm = new StoriesViewModel($data, 'all');

        require __DIR__ . '/../Views/Stories/Detail.php';
    }


    public function apiStories(): void
    {
        $day  = $this->normalizeDay($_GET['day'] ?? 'all');
        $data = $this->storiesService->getStoriesHomeData($day);

        $stories = $data['stories'] ?? [];

        // Only expose the fields the frontend needs — never leak internal IDs blindly
        $output = array_map(function (array $s): array {
            return [
                'story_id'    => (int)($s['story_id']   ?? 0),
                'name'        => $s['story_name']  ?? $s['name']       ?? '',
                'description' => $s['description'] ?? '',
                'image_path'  => $s['image_path']  ?? '',
                'story_type'  => $s['story_type']  ?? '',
                'age'         => $s['age']         ?? '',
                'language'    => $s['language']    ?? '',
                'template'    => $s['template']    ?? 'generic',
                'audience'    => $s['audience']    ?? '',
                'event_day'   => $s['event_day']   ?? '',
                'start_time'  => $s['start_time']  ?? '',
                'venue_name'  => $s['venue_name']  ?? '',
                'venue_city'  => $s['venue_city']  ?? '',
            ];
        }, $stories);

        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => true,
            'day'     => $day,
            'count'   => count($output),
            'stories' => $output,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function normalizeDay(string $input): string
    {
        $day = strtolower(trim($input));
        return in_array($day, self::ALLOWED_DAYS, true) ? $day : 'all';
    }
}