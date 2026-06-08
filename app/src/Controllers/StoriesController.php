<?php

namespace App\Controllers;

use App\Repositories\StoriesRepository;
use App\Repositories\StoriesSettingsRepository;
use App\Services\StoriesService;
use App\ViewModels\StoriesViewModel;
use App\Exceptions\NotFoundException;

class StoriesController
{
    /**
     * @var StoriesService Service for story business logic
     */
    private StoriesService $storiesService;

    /**
     * @var StoriesSettingsRepository Repository for stories settings
     */
    private StoriesSettingsRepository $settingsRepo;

    public function __construct()
    {
        // Using a Service to keep business logic separate
        $this->storiesService = new StoriesService(new StoriesRepository());
        $this->settingsRepo = new StoriesSettingsRepository();
    }

    /**
     * Display stories home page
     * @return void
     */
    public function home(): void
    {
        // Get first 3 stories for featured cards on home page
        $allStories = $this->storiesService->getStoriesHomeData('all')['stories'] ?? [];
        $featured = array_slice($allStories, 0, 3);
        
        // Fetch settings from database
        $settings = $this->settingsRepo->getAll();
        
        $data = [
            'featured' => $featured,
            'pageTitle' => 'Stories in Haarlem',
            'settings' => $settings,
        ];
        $vm = new StoriesViewModel($data, 'all');
        require __DIR__ . '/../Views/Stories/Home.php';
    }

    /**
     * Display stories filtered by day
     * @return void
     */
    public function events(): void
    {
        // Get the day filter from user
        $day  = $this->normalizeDay($_GET['day'] ?? 'all');
        
        // Fetch stories and prepare for display
        $data = $this->storiesService->getStoriesHomeData($day);
        $data['pageTitle'] = 'Stories in Haarlem';
        
        // Fetch settings from database
        $settings = $this->settingsRepo->getAll();
        $data['settings'] = $settings;
        
        $vm = new StoriesViewModel($data, $day);
        require __DIR__ . '/../Views/Stories/Events.php';
    }

    /**
     * Display single story detail page
     * @return void
     * @throws NotFoundException If story ID is invalid or story not found
     */
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


    /**
     * Provide stories data as JSON API endpoint
     * @return void Outputs JSON and exits
     */
    public function apiStories(): void
    {
        $day  = $this->normalizeDay($_GET['day'] ?? 'all');
        $data = $this->storiesService->getStoriesHomeData($day);

        $stories = $data['stories'] ?? [];

        $output = array_map(function (array $s): array {
            return [
                'story_id'    => (int)($s['story_id']   ?? 0),
                'ticket_details_id' => (int)($s['ticket_details_id'] ?? 0),
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

     /**
     * @var array Allowed day values for filtering
     */
    private const ALLOWED_DAYS = ['all', 'thursday', 'friday', 'saturday', 'sunday'];


    /**
     * Normalize and validate day input
     */
    private function normalizeDay(string $input): string
    {
        $day = strtolower(trim($input));
        return in_array($day, self::ALLOWED_DAYS, true) ? $day : 'all';
    }
}
