<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validates MIME/size, then moves admin uploads into predictable folders under `public/`.
 * Returned strings are what you store in the DB (relative paths like `uploads/stories/...`).
 * Follows the same pattern as JazzAdminUploadService.
 */
final class StoriesAdminUploadService
{
    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;  // 10 MB

    /** @var array<string, string> */
    private const IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private string $publicDir;

    /**
     * Points at the project's `public/` folder so we know where to write files.
     */
    public function __construct()
    {
        $this->publicDir = dirname(__DIR__, 2) . '/public';
    }

    /**
     * Story image (hero, thumbnail, etc.) — stored under `public/images/stories/uploads/`.
     * Returns relative path to store in DB.
     *
     * @param array<string, mixed>|null $file $_FILES['image_upload']
     * @param string $prefix Prefix for filename (e.g., 'story', 'hero')
     */
    public function storeStoryImage(?array $file, string $prefix = 'story'): ?string
    {
        $prefix = preg_replace('/[^a-z0-9\-_]/i', '', $prefix) ?: 'story';
        $v = $this->validateUpload($file, self::MAX_IMAGE_BYTES, self::IMAGE_MIME, 'Story image');
        if ($v === null) {
            return null;
        }

        $dir = $this->publicDir . '/images/stories/uploads';
        $this->ensureDir($dir);
        $name = $prefix . '-' . bin2hex(random_bytes(6)) . '.' . $v['ext'];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($v['tmp'], $dest)) {
            throw new \RuntimeException('Could not save story image.');
        }

        return 'uploads/stories/' . $name;
    }

    /**
     * Validates uploaded file: checks if it exists, is readable, has allowed MIME type, and is within size limit.
     *
     * @param array<string, mixed>|null $file
     * @param int $maxBytes
     * @param array<string, string> $allowedMime
     * @param string $fieldName For error messages
     *
     * @return array{tmp: string, ext: string}|null Returns temp path + extension, or null if no file or validation fails
     */
    private function validateUpload(?array $file, int $maxBytes, array $allowedMime, string $fieldName): ?array
    {
        if (
            $file === null
            || !isset($file['tmp_name'], $file['name'], $file['size'], $file['error'])
            || (int) $file['error'] !== UPLOAD_ERR_OK
        ) {
            return null;
        }

        $tmp = (string) $file['tmp_name'];
        $name = (string) $file['name'];
        $size = (int) $file['size'];

        if (!is_uploaded_file($tmp)) {
            throw new \RuntimeException("$fieldName: Not an uploaded file.");
        }
        if ($size > $maxBytes) {
            throw new \RuntimeException("$fieldName: File too large (max " . ($maxBytes / 1024 / 1024) . " MB).");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            throw new \RuntimeException("$fieldName: File type not allowed ($mime). Allowed: " . implode(', ', array_keys($allowedMime)));
        }

        $ext = $allowedMime[$mime];

        return ['tmp' => $tmp, 'ext' => $ext];
    }

    /**
     * Ensures a directory exists; creates it recursively if needed.
     */
    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Could not create directory: $dir");
            }
        }
    }
}
