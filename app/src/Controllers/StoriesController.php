<?php

namespace App\Controllers;

use App\Repositories\StoriesRepository;
use App\Services\StoriesService;
use App\ViewModels\StoriesViewModel;

class StoriesController
{
    private const ALLOWED_DAYS = ['all', 'thursday', 'friday', 'saturday', 'sunday'];

    public function index(): void
    {
        $day = $this->normalizeDay($_GET['day'] ?? 'all'); //get day from URL

        $service = new StoriesService(new StoriesRepository());
        $data = $service->getStoriesHomeData($day);

        $data['pageTitle'] = 'Stories in Haarlem';
        $vm = new StoriesViewModel($data, $day);

        $storiesHeroImages = [
            '/images/Stories/stories-home-image-main1.png',
            '/images/Stories/stories-home-image-main2.jpg',
            '/images/Stories/stories-home-image-main3.jpg',
        ];

        require __DIR__ . '/../Views/Stories/Index.php';  //help to load the view and display browser
    }

    public function venue(): void
    {
        $slug = trim((string)($_GET['slug'] ?? ''));  //identifier 
        $day  = $this->normalizeDay($_GET['day'] ?? 'all');

        if ($slug === '') {
            http_response_code(404);
            echo "Venue slug is required.";
            exit;
        }

        $service = new StoriesService(new StoriesRepository());
        $data = $service->getVenuePageData($slug, $day);

        if (empty($data['venue'])) {
            http_response_code(404);
            echo "Venue not found.";
            exit;
        }

        $data['pageTitle'] = $data['venue']['name'] ?? 'Venue'; //if exit use title or default
        $vm = new StoriesViewModel($data, $day);

        $venueHero = $this->venueHeroImage((int)($data['venue']['venue_id'] ?? 0));

        require __DIR__ . '/../Views/Stories/Venue.php';
    }

    public function detail(): void
    {
        //story_id (from stories table)
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($id === false || $id === 0) {
            http_response_code(404);
            echo "Invalid or missing story ID.";
            exit;
        }

        $service = new StoriesService(new StoriesRepository());
        $data = $service->getStoryDetailData((int)$id);

        if (empty($data['story'])) {
            http_response_code(404);
            echo "Story not found.";
            exit;
        }

        $data['pageTitle'] = $data['story']['name'] ?? 'Story Details';
        $vm = new StoriesViewModel($data, 'all');

        require __DIR__ . '/../Views/Stories/Detail.php';
    }

    private function normalizeDay(string $input): string
    {
        $day = strtolower(trim($input));
        return in_array($day, self::ALLOWED_DAYS, true) ? $day : 'all';
    }

    private function venueHeroImage(int $venueId): string
    {
        return match ($venueId) {
            10 => '/images/Stories/venues/de-schuur-heroimage.jpg',
            11 => '/images/Stories/venues/Kweekcafe-heroimage.jpg',
            default => '/images/Stories/venues/default-venue.jpg',
        };
    }
}