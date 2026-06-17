<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\StoriesRepository;
use App\Services\StoriesService;
use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

class AdminStoriesController
{
    private StoriesService $storiesService;

    public function __construct()
    {
        $this->storiesService = new StoriesService(new StoriesRepository());
    }

    public function index(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $stories = $this->storiesService->getAllStoriesForAdminWithDetailStatus();
        $csrf = Csrf::token('admin_stories_delete');

        require __DIR__ . '/../Views/Admin/Stories/Index.php';
    }

    public function edit(): void
    {
        // Only admins can edit
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $storyId = (int)($_GET['id'] ?? 0);
        $story = $this->storiesService->getStoryForEdit($storyId);

        if (!$story) {
            throw new NotFoundException('Story not found.');
        }

        $errors = [];
        // CSRF token prevents form hacking
        $csrf = Csrf::token('admin_stories_edit');

        require __DIR__ . '/../Views/Admin/Stories/Edit.php';
    }

    public function update(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        // Stops another site from submitting this admin form.
        if (!Csrf::validate('admin_stories_edit', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /admin/stories');
            exit;
        }

        $data = $this->storiesService->prepareStoryData($_POST, $_FILES);
        $this->validateAndSaveStory($data, 'update', 'admin_stories_edit', 'Story updated successfully.');
    }

    public function delete(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        // Stops another site from deleting a story for this admin.
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
        $data = $this->storiesService->getDetailPageFormData($slug);

        $story      = $data['story'];
        $detailPage = $data['detailPage'];
        $highlights = $data['highlights'];
        $gallery    = $data['gallery'];

        require __DIR__ . '/../Views/Admin/Stories/DetailPageForm.php';
    }

    public function saveDetailPage(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $storyId = (int)($_POST['story_id'] ?? 0);
        $data = $this->storiesService->prepareDetailPageData($_POST, $_FILES);

        try {
            $this->storiesService->saveDetailPage($storyId, $data);
        } catch (\Exception $e) {
            Session::setFlash('admin_error', 'Error saving detail page: ' . $e->getMessage());
            header('Location: /cms/stories');
            exit;
        }

        Session::setFlash('admin_success', 'Detail page saved successfully.');
        header('Location: /admin/stories');
        exit;
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

        // Stops another site from submitting this admin form.
        if (!Csrf::validate('admin_stories_create', $_POST['_csrf'] ?? null)) {
            Session::setFlash('admin_error', 'Invalid request. Please try again.');
            header('Location: /cms/stories');
            exit;
        }

        $data = $this->storiesService->prepareStoryData($_POST, $_FILES);
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
        $storyId = (int)($_POST['story_id'] ?? 0);

        try {
            $this->storiesService->saveStoryFromForm($storyId, $data, $operation === 'create');
        } catch (ValidationException $e) {
            $story = $operation === 'create' ? [] : array_merge(['story_id' => $storyId], $data);
            $errors = $e->getErrors();
            $csrf = Csrf::token($csrfToken);
            require __DIR__ . '/../Views/Stories/Admin/Edit.php';
            return;
        } catch (\Exception $e) {
            Session::setFlash('admin_error', 'Error: ' . $e->getMessage());
            header('Location: /cms/stories');
            exit;
        }

        Session::setFlash('admin_success', $successMessage);
        header('Location: /cms/stories');
        exit;
    }
}
