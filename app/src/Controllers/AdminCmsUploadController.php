<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;

final class AdminCmsUploadController
{
    private const MAX_BYTES = 5 * 1024 * 1024;

    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!AdminAuth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Forbidden']);

            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'error' => 'Method not allowed']);

            return;
        }

        if (!Csrf::validateWithoutConsuming('cms_upload', $_POST['_csrf'] ?? null)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Bad CSRF token, reload the page.']);

            return;
        }

        $context = (string) ($_POST['context'] ?? '');
        $file = $_FILES['image'] ?? null;

        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Upload failed.']);

            return;
        }

        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'File too big (max 5 MB).']);

            return;
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid file.']);

            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!is_string($mime) || !isset(self::ALLOWED[$mime])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Only jpg/png/gif/webp.']);

            return;
        }

        $ext = self::ALLOWED[$mime];
        $basename = 'cms-' . bin2hex(random_bytes(8)) . '.' . $ext;

        if ($context === 'home_about') {
            $dir = dirname(__DIR__, 2) . '/public/images/cms/home';
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Could not create folder.']);

                return;
            }
            $dest = $dir . '/' . $basename;
            if (!move_uploaded_file($tmp, $dest)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Save failed.']);

                return;
            }
            $url = '/images/cms/home/' . rawurlencode($basename);
            echo json_encode(['ok' => true, 'url' => $url, 'filename' => $basename]);

            return;
        }

        if ($context === 'dance_hero') {
            $dir = dirname(__DIR__, 2) . '/public/images/dance';
            if (!is_dir($dir)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'images/dance missing.']);

                return;
            }
            $dest = $dir . '/' . $basename;
            if (!move_uploaded_file($tmp, $dest)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Save failed.']);

                return;
            }
            echo json_encode(['ok' => true, 'filename' => $basename, 'url' => '/images/dance/' . rawurlencode($basename)]);

            return;
        }

        if ($context === 'history_hero') {
            $dir = dirname(__DIR__, 2) . '/public/images/history';
            if (!is_dir($dir)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'images/history missing.']);
                return;
            }
            $dest = $dir . '/' . $basename;
            if (!move_uploaded_file($tmp, $dest)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'error' => 'Save failed.']);
                return;
            }

            // Insert into history_images and get new image_id
            $service = new \App\Services\HistoryService(new \App\Repositories\HistoryRepository());
            $imageUrl = '/images/history/' . rawurlencode($basename);
            $imageId = $service->insertImage($imageUrl, $basename);

            echo json_encode(['ok' => true, 'image_id' => $imageId, 'url' => $imageUrl]);
            return;
        }

        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Unknown context.']);
    }
}
