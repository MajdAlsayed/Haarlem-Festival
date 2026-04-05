<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Contracts\ServiceInterface\HistoryServiceInterface;
use App\Repositories\HistoryRepository;
use App\Services\HistoryService;
use App\ViewModels\AdminHistoryViewModel;
use App\Core\HtmlSanitizer;

final class AdminHistoryController
{
    private HistoryServiceInterface $historyService;

    public function __construct()
    {
        $this->historyService = new HistoryService(new HistoryRepository());
    }

    public function showIndexForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $result = $this->historyService->getPageBlocksList('history');

        $viewModel = new AdminHistoryViewModel(
            csrf: Csrf::token('cms_history'),
            blocks: $result['blocks'],
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/History/HistoryEdit.php';
    }

    public function saveIndex(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('cms_history', $_POST['_csrf'] ?? null)) {
            $this->showIndexForm('Wrong CSRF token.', null);
            return;
        }

        $blocks = $this->sanitizeBlocks($_POST['blocks'] ?? []);

        // Try to save block, if not - shows error
        foreach ($blocks as $blockId => $content) {
            if (!$this->historyService->updatePageBlock((int)$blockId, $content)) {
                $this->showIndexForm('Could not save block.', null);
                return;
            }
        }
        $this->showIndexForm(null, 'Saved.');
    }

    public function showLocationsForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $result = $this->historyService->getPageBlocksList('history-locations');

        $viewModel = new AdminHistoryViewModel(
            csrf: Csrf::token('cms_history'),
            blocks: $result['blocks'],
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/History/HistoryLocationsEdit.php';
    }

    public function saveLocations(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('cms_history', $_POST['_csrf'] ?? null)) {
            $this->showLocationsForm('Wrong CSRF token.', null);
            return;
        }

        $blocks = $this->sanitizeBlocks($_POST['blocks'] ?? []);

        // Try to save block, if not - shows error
        foreach ($blocks as $blockId => $content) {
            if (!$this->historyService->updatePageBlock((int)$blockId, $content)) {
                $this->showLocationsForm('Could not save block.', null);
                return;
            }
        }
        $this->showLocationsForm(null, 'Saved.');
    }

    public function showToursForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $result = $this->historyService->getPageBlocksList('history-tours');

        $viewModel = new AdminHistoryViewModel(
            csrf: Csrf::token('cms_history'),
            blocks: $result['blocks'],
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/History/HistoryToursEdit.php';
    }

    public function saveTours(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('cms_history', $_POST['_csrf'] ?? null)) {
            $this->showToursForm('Wrong CSRF token.', null);
            return;
        }

        $blocks = $this->sanitizeBlocks($_POST['blocks'] ?? []);

        // Try to save block, if not - shows error
        foreach ($blocks as $blockId => $content) {
            if (!$this->historyService->updatePageBlock((int)$blockId, $content)) {
                $this->showToursForm('Could not save block.', null);
                return;
            }
        }
        $this->showToursForm(null, 'Saved.');
    }

    public function showLocationForm(string $slug, ?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $location = $this->historyService->getLocationBySlug($slug);
        $result = $this->historyService->getPageBlocksList($location->pageSlug);

        $viewModel = new AdminHistoryViewModel(
            csrf: Csrf::token('cms_history'),
            slug: $slug,
            blocks: $result['blocks'],
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/History/HistoryLocationEdit.php';
    }

    public function saveLocation(string $slug): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('cms_history', $_POST['_csrf'] ?? null)) {
            $this->showLocationForm($slug, 'Wrong CSRF token.', null);
            return;
        }

        $blocks = $this->sanitizeBlocks($_POST['blocks'] ?? []);

        // Try to save block, if not - shows error
        foreach ($blocks as $blockId => $content) {
            if (!$this->historyService->updatePageBlock((int)$blockId, $content)) {
                $this->showLocationForm($slug, 'Could not save block.', null);
                return;
            }
        }
        $this->showLocationForm($slug, null, 'Saved.');
    }

    private function sanitizeBlocks(array $blocks): array
    {
        foreach ($blocks as $blockId => &$content) {
            if (isset($content['description'])) {
                $content['description'] = HtmlSanitizer::purify($content['description']);
            }
            if (isset($content['text'])) {
                $content['text'] = HtmlSanitizer::purify($content['text']);
            }
        }
        unset($content);
        return $blocks;
    }

    public function getImages(): void
    {
        if (!AdminAuth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['ok' => false]);
            return;
        }

        header('Content-Type: application/json');
        $images = $this->historyService->getAllImages();
        echo json_encode(['ok' => true, 'images' => $images]);
    }
}