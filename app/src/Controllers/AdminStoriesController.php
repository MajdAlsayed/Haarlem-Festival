<?php

namespace App\Controllers;

use App\Repositories\StoriesRepository;
use App\Services\StoriesService;
use App\Validation\StoryValidator;
use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\Session;

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

        $stories = $this->storiesService->getAllStoriesForAdmin();

        foreach ($stories as &$story) {
            $story['has_detail_page'] = $this->storiesService->hasDetailPage(
                (int)($story['story_id'] ?? 0)
            );
        }
        unset($story);

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
            http_response_code(404);
            echo 'Story not found.';
            return;
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
            header('Location: /cms/stories');
            exit;
        }

        $validator = new StoryValidator();
        $storyId   = (int)($_POST['story_id'] ?? 0);

        $data = [
            'name'        => trim((string)($_POST['name']        ?? '')),
            'slug'        => trim((string)($_POST['slug']        ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'image_path'  => trim((string)($_POST['image_path']  ?? '')),
            'story_type'  => trim((string)($_POST['story_type']  ?? '')),
            'age'         => trim((string)($_POST['age']         ?? '')),
            'language'    => trim((string)($_POST['language']    ?? '')),
            'template'    => trim((string)($_POST['template']    ?? 'generic')),
            'audience'    => trim((string)($_POST['audience']    ?? '')),
            'event_id'    => (int)($_POST['event_id'] ?? 0),
        ];

        $errors = $validator->validateStory($data);

        if (!empty($errors)) {
            $story = array_merge(['story_id' => $storyId], $data);
            $csrf = Csrf::token('admin_stories_edit');
            require __DIR__ . '/../Views/Stories/Admin/Edit.php';
            return;
        }

        $this->storiesService->updateStory($storyId, $data);

        Session::setFlash('admin_success', 'Story updated successfully.');
        header('Location: /cms/stories');
        exit;
    }


    public function delete(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }
        $storyId = (int)($_POST['story_id'] ?? 0);

        if ($storyId > 0) {
            $this->storiesService->deleteStory($storyId);
        }

        header('Location: /cms/stories');
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
            http_response_code(404);
            echo 'Story not found.';
            return;
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

        $data = [
            'hero_image'            => trim((string)($_POST['hero_image']            ?? '')),
            'hero_heading'          => trim((string)($_POST['hero_heading']          ?? '')),
            'hero_description'      => trim((string)($_POST['hero_description']      ?? '')),
            'article_title'         => trim((string)($_POST['article_title']         ?? '')),
            'article_image'         => trim((string)($_POST['article_image']         ?? '')),
            'article_image_caption' => trim((string)($_POST['article_image_caption'] ?? '')),
            'article_paragraph_1'   => trim((string)($_POST['article_paragraph_1']   ?? '')),
            'article_paragraph_2'   => trim((string)($_POST['article_paragraph_2']   ?? '')),
            'article_paragraph_3'   => trim((string)($_POST['article_paragraph_3']   ?? '')),
            'highlights'            => $_POST['highlights'] ?? [],
            'gallery'               => $_POST['gallery']    ?? [],
        ];

        $this->storiesService->saveDetailPage($storyId, $data);

        header('Location: /cms/stories');
        exit;
    }
}