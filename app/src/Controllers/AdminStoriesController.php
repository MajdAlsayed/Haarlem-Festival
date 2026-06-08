<?php

namespace App\Controllers;

use App\Repositories\StoriesRepository;
use App\Services\StoriesService;
use App\Services\StoriesAdminUploadService;
use App\Validation\StoryValidator;
use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class AdminStoriesController
{
    private StoriesService $storiesService;
    private StoriesAdminUploadService $uploader;

    public function __construct()
    {
        $this->storiesService = new StoriesService(new StoriesRepository());
        $this->uploader = new StoriesAdminUploadService();
    }

    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $stories = $this->storiesService->getAllStoriesForAdmin();

        foreach ($stories as &$story) {
            $story['has_detail_page'] = $this->storiesService->hasDetailPage(
                (int)($story['story_id'] ?? 0)
            );
        }
        unset($story);

        $csrf = Csrf::token('admin_stories_delete');

        require __DIR__ . '/../Views/Stories/Admin/Index.php';
    }

    public function edit(): void
    {
        // Only admins can edit
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $storyId = (int)($_GET['id'] ?? 0);
        $story   = $this->storiesService->getStoryForEdit($storyId);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        $errors = [];
        // CSRF token prevents form hacking
        $csrf = Csrf::token('admin_stories_edit');

        require __DIR__ . '/../Views/Stories/Admin/Edit.php';
    }

    public function update(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('admin_stories_edit', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/stories');
            exit;
        }

        $data = $this->prepareStoryData();
        $this->validateAndSaveStory($data, 'update', 'admin_stories_edit', 'Story updated successfully.');
    }


    public function delete(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('admin_stories_delete', $_POST['_csrf'] ?? null)) {
            Session::setFlash('error', 'Invalid request token');
            header('Location: /admin/stories');
            exit;
        }

        $storyId = (int)($_POST['story_id'] ?? 0);

        if ($storyId > 0) {
            $this->storiesService->deleteStory($storyId);
        }

        header('Location: /admin/stories');
        exit;
    }

    public function editDetailPage(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $slug = trim((string)($_GET['slug'] ?? ''));
        $data = $this->storiesService->getDetailPageForCms($slug);

        if (empty($data['story'])) {
            throw new NotFoundException('Story not found.');
        }

        $story      = $data['story'];
        $detailPage = is_array($data['detailPage'] ?? null) ? $data['detailPage'] : [];

        $highlights = json_decode((string)($detailPage['highlights'] ?? '[]'), true);
        $gallery    = json_decode((string)($detailPage['gallery']    ?? '[]'), true);

        if (!is_array($highlights)) { $highlights = []; }
        if (!is_array($gallery))    { $gallery    = []; }

        require __DIR__ . '/../Views/Stories/Admin/DetailPageForm.php';
    }

    public function saveDetailPage(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $storyId = (int)($_POST['story_id'] ?? 0);

        // Handle hero image 
        $uploadedHeroImage = $this->uploader->storeStoryImage(
            isset($_FILES['hero_image_upload']) && is_array($_FILES['hero_image_upload']) ? $_FILES['hero_image_upload'] : null,
            'detail-hero'
        );
        $heroImage = $uploadedHeroImage !== null ? $uploadedHeroImage : trim((string)($_POST['hero_image'] ?? ''));


        // Handle article image 
        $uploadedArticleImage = $this->uploader->storeStoryImage(
            isset($_FILES['article_image_upload']) && is_array($_FILES['article_image_upload']) ? $_FILES['article_image_upload'] : null,
            'detail-article'
        );

        
        $articleImage = $uploadedArticleImage !== null ? $uploadedArticleImage : trim((string)($_POST['article_image'] ?? ''));

        $data = [
            'hero_image'            => $heroImage,
            'hero_heading'          => trim((string)($_POST['hero_heading']          ?? '')),
            'hero_description'      => trim((string)($_POST['hero_description']      ?? '')),
            'article_title'         => trim((string)($_POST['article_title']         ?? '')),
            'article_image'         => $articleImage,
            'article_image_caption' => trim((string)($_POST['article_image_caption'] ?? '')),
            'article_paragraph_1'   => trim((string)($_POST['article_paragraph_1']   ?? '')),
            'article_paragraph_2'   => trim((string)($_POST['article_paragraph_2']   ?? '')),
            'article_paragraph_3'   => trim((string)($_POST['article_paragraph_3']   ?? '')),
            'highlights'            => $_POST['highlights'] ?? [],
            'gallery'               => $_POST['gallery']    ?? [],
        ];

        try {
            $this->storiesService->saveDetailPage($storyId, $data);
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', 'Error saving detail page: ' . $e->getMessage());
            header('Location: /cms/stories');
            exit;
        }

        Session::setFlash('admin_success', 'Detail page saved successfully.');
        header('Location: /admin/stories');
        exit;
    }

    /**
     * Prepares story data from POST request with image upload handling.
     * @return array<string, mixed>
     */
    private function prepareStoryData(): array
    {
        // Handle image upload if provided
        $uploadedImage = $this->uploader->storeStoryImage(
            isset($_FILES['image_upload']) && is_array($_FILES['image_upload']) ? $_FILES['image_upload'] : null,
            'story'
        );
        $imagePath = $uploadedImage !== null ? $uploadedImage : trim((string)($_POST['image_path'] ?? ''));

        return [
            'name'        => trim((string)($_POST['name']        ?? '')),
            'slug'        => trim((string)($_POST['slug']        ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'image_path'  => $imagePath,
            'story_type'  => trim((string)($_POST['story_type']  ?? '')),
            'age'         => trim((string)($_POST['age']         ?? '')),
            'language'    => trim((string)($_POST['language']    ?? '')),
            'template'    => trim((string)($_POST['template']    ?? 'generic')),
            'audience'    => trim((string)($_POST['audience']    ?? '')),
            'event_id'    => (int)($_POST['event_id'] ?? 0),
        ];
    }
        public function create(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $story = [];
        $data = [];
        $errors = [];
        $csrf = Csrf::token('admin_stories_create');

        require __DIR__ . '/../Views/Stories/Admin/Edit.php';
    }

    public function store(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('admin_stories_create', $_POST['_csrf'] ?? null))
      {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /cms/stories');
            exit;
        }

        $data = $this->prepareStoryData();
        $this->validateAndSaveStory($data, 'create', 'admin_stories_create', 'Story created successfully.');
    }

    /**
     * Validates and saves story data. Handles both create and update operations.
     *
     * @param array<string, mixed> $data Story data to validate and save
     * @param string $operation 'create' or 'update'
     * @param string $csrfToken CSRF token name for form
     * @param string $successMessage Message to show on success
     */



    private function validateAndSaveStory(array $data, string $operation, string $csrfToken, string $successMessage): void
    {
        $validator = new StoryValidator();
        $storyId = (int)($_POST['story_id'] ?? 0);

        try {
            $validator->validateStory($data);
            
            if ($operation === 'create') {
                $this->storiesService->createStory($data);
            } else {
                $this->storiesService->updateStory($storyId, $data);
            }
        } catch (ValidationException $e) {
            $story = $operation === 'create' ? [] : array_merge(['story_id' => $storyId], $data);
            $errors = $e->getErrors();
            $csrf = Csrf::token($csrfToken);
            require __DIR__ . '/../Views/Stories/Admin/Edit.php';
            return;
        } catch (\Throwable $e) {
            Session::setFlash('admin_error', 'Error: ' . $e->getMessage());
            header('Location: /cms/stories');
            exit;
        }

        Session::setFlash('admin_success', $successMessage);
        header('Location: /cms/stories');
        exit;
    }
}