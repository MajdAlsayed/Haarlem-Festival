<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AppException;
use App\Exceptions\NotFoundException;
use App\Repositories\SettingsRepository;
use App\Repositories\StoriesRepository;
use App\Repositories\StoriesSettingsRepository;
use App\Services\StoriesService;
use App\ViewModels\StoriesViewModel;

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
    private SettingsRepository $appSettingsRepo;

    public function __construct(
        ?StoriesService $storiesService = null,
        ?StoriesSettingsRepository $settingsRepo = null,
        ?SettingsRepository $appSettingsRepo = null
    ) {
        $this->storiesService = $storiesService ?? new StoriesService(new StoriesRepository());
        $this->settingsRepo = $settingsRepo ?? new StoriesSettingsRepository();
        $this->appSettingsRepo = $appSettingsRepo ?? new SettingsRepository();
    }

    /**
     * Display stories home page
     * @return void
     */
    public function home(): void
    {
        try {
            $data = $this->addCommonPageData($this->storiesService->getStoriesHomePageData());

            $vm = new StoriesViewModel($data, 'all');
            require __DIR__ . '/../Views/Stories/Home.php';
        } catch (\Exception $e) {
            $this->handleControllerError($e, 'Unable to load stories home page.');
        }
    }

    /**
     * Display stories filtered by day
     * @return void
     */
    public function events(): void
    {
        try {
            $data = $this->addCommonPageData(
                $this->storiesService->getStoriesEventsPageData((string)($_GET['day'] ?? 'all'))
            );
            $day = $data['selectedDay'];
            $vm = new StoriesViewModel($data, $day);

            require __DIR__ . '/../Views/Stories/Events.php';
        } catch (\Exception $e) {
            $this->handleControllerError($e, 'Unable to load stories events page.');
        }
    }

    /**
     * Display single story detail page
     * @return void
     */
    public function detail(): void
    {
        try {
            $id = $this->getStoryIdFromRequest();

            if ($id === null) {
                throw new NotFoundException('Invalid or missing story ID.');
            }

            $data = $this->storiesService->getStoryDetailData($id);
            $data['pageTitle'] = $data['story']['name'] ?? 'Story Details';
            $data = $this->addCommonPageData($data);
            $vm = new StoriesViewModel($data, 'all');

            require __DIR__ . '/../Views/Stories/Detail.php';
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->handleControllerError($e, 'Unable to load story detail page.');
        }
    }

    /**
     * Provide stories data as JSON API endpoint
     * @return void Outputs JSON and exits
     */
    public function apiStories(): void
    {
        try {
            $data = $this->storiesService->getStoriesApiData((string)($_GET['day'] ?? 'all'));

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (\Exception $e) {
            $this->handleApiError($e, 'Unable to load stories.');
        }
    }

    private function addCommonPageData(array $data): array
    {
        $data['pageTitle'] = $data['pageTitle'] ?? 'Stories in Haarlem';
        $data['settings'] = $data['settings'] ?? $this->settingsRepo->getAll();
        $data['appSettings'] = $data['appSettings'] ?? $this->appSettingsRepo->getAll();

        return $data;
    }

    private function getStoryIdFromRequest(): ?int
    {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false || $id === 0 ? null : (int)$id;
    }

    private function handleControllerError(\Exception $e, string $message): void
    {
        error_log($message . ' ' . $e->getMessage());

        throw new AppException($message, 0, $e);
    }

    private function handleApiError(\Exception $e, string $message): void
    {
        error_log($message . ' ' . $e->getMessage());
        $this->clearOutputBuffer();

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => false,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function clearOutputBuffer(): void
    {
        if (ob_get_length() !== false && ob_get_length() > 0) {
            ob_clean();
        }
    }

}
