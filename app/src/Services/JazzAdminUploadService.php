<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validates MIME/size, then moves admin uploads into predictable folders under `public/`.
 *
 * Returned strings are what you store in the DB (relative paths like `uploads/layout/...` or `jazz-uploads/...`).
 */
final class JazzAdminUploadService
{
    private const MAX_IMAGE_BYTES = 10 * 1024 * 1024;

    private const MAX_AUDIO_BYTES = 50 * 1024 * 1024;

    /** @var array<string, string> */
    private const IMAGE_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    /** @var array<string, string> */
    private const AUDIO_MIME = [
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/wave' => 'wav',
        'audio/flac' => 'flac',
        'audio/x-flac' => 'flac',
        'audio/ogg' => 'ogg',
        'application/ogg' => 'ogg',
        'audio/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
    ];

    private string $publicDir;

    /** Points at the project’s `public/` folder so we know where to write files. */
    public function __construct()
    {
        $this->publicDir = dirname(__DIR__, 2) . '/public';
    }

    /**
     * Short clip tied to a jazz event — stored under `public/audio/jazz-uploads/preview/`; return value is relative to `/audio/`.
     *
     * @param array<string, mixed>|null $file $_FILES['preview_audio_upload']
     */
    public function storePreviewAudio(?array $file): ?string
    {
        $v = $this->validateUpload($file, self::MAX_AUDIO_BYTES, self::AUDIO_MIME, 'Preview audio');
        if ($v === null) {
            return null;
        }

        $dir = $this->publicDir . '/audio/jazz-uploads/preview';
        $this->ensureDir($dir);
        $name = 'ev-' . bin2hex(random_bytes(8)) . '.' . $v['ext'];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($v['tmp'], $dest)) {
            throw new \RuntimeException('Could not save preview audio file.');
        }

        return 'jazz-uploads/preview/' . $name;
    }

    /**
     * Album art for a discography row — lives under `public/images/jazz/uploads/discography/`.
     *
     * @param array<string, mixed>|null $file $_FILES['disc_image_upload']
     */
    public function storeDiscographyCover(?array $file): ?string
    {
        $v = $this->validateUpload($file, self::MAX_IMAGE_BYTES, self::IMAGE_MIME, 'Cover image');
        if ($v === null) {
            return null;
        }

        $dir = $this->publicDir . '/images/jazz/uploads/discography';
        $this->ensureDir($dir);
        $name = 'cover-' . bin2hex(random_bytes(8)) . '.' . $v['ext'];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($v['tmp'], $dest)) {
            throw new \RuntimeException('Could not save cover image.');
        }

        return 'uploads/discography/' . $name;
    }

    /**
     * Band headshot — `public/images/jazz/uploads/band-members/`.
     *
     * @param array<string, mixed>|null $file $_FILES['band_photo_upload']
     */
    public function storeBandMemberPhoto(?array $file): ?string
    {
        $v = $this->validateUpload($file, self::MAX_IMAGE_BYTES, self::IMAGE_MIME, 'Band photo');
        if ($v === null) {
            return null;
        }

        $dir = $this->publicDir . '/images/jazz/uploads/band-members';
        $this->ensureDir($dir);
        $name = 'member-' . bin2hex(random_bytes(8)) . '.' . $v['ext'];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($v['tmp'], $dest)) {
            throw new \RuntimeException('Could not save band member photo.');
        }

        return 'uploads/band-members/' . $name;
    }

    /**
     * Full track audio for discography — `public/audio/jazz-uploads/discography/`.
     *
     * @param array<string, mixed>|null $file $_FILES['disc_audio_upload']
     */
    public function storeDiscographyTrackAudio(?array $file): ?string
    {
        $v = $this->validateUpload($file, self::MAX_AUDIO_BYTES, self::AUDIO_MIME, 'Track audio');
        if ($v === null) {
            return null;
        }

        $dir = $this->publicDir . '/audio/jazz-uploads/discography';
        $this->ensureDir($dir);
        $name = 'track-' . bin2hex(random_bytes(8)) . '.' . $v['ext'];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($v['tmp'], $dest)) {
            throw new \RuntimeException('Could not save track audio file.');
        }

        return 'jazz-uploads/discography/' . $name;
    }

    /**
     * Hero / placeholder / per-artist hero from the big settings form — `uploads/layout/` with a safe prefix.
     *
     * @param array<string, mixed>|null $file
     */
    public function storeLayoutImage(?array $file, string $prefix): ?string
    {
        $prefix = preg_replace('/[^a-z0-9\-_]/i', '', $prefix) ?: 'img';
        $v = $this->validateUpload($file, self::MAX_IMAGE_BYTES, self::IMAGE_MIME, 'Image');
        if ($v === null) {
            return null;
        }

        $dir = $this->publicDir . '/images/jazz/uploads/layout';
        $this->ensureDir($dir);
        $name = $prefix . '-' . bin2hex(random_bytes(6)) . '.' . $v['ext'];
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($v['tmp'], $dest)) {
            throw new \RuntimeException('Could not save image.');
        }

        return 'uploads/layout/' . $name;
    }

    /**
     * Shared checks: no file → null; OK → tmp path + extension; bad size/type → exception.
     *
     * @param array<string, string> $mimeToExt
     * @return ?array{tmp: string, ext: string}
     */
    private function validateUpload(?array $file, int $maxBytes, array $mimeToExt, string $label): ?array
    {
        if ($file === null || !isset($file['error'])) {
            return null;
        }
        $err = (int) $file['error'];
        if ($err === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($err !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException("{$label} upload failed (code {$err}).");
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size > $maxBytes) {
            throw new \InvalidArgumentException(
                $label . ' is too large (max ' . (int) round($maxBytes / 1024 / 1024) . ' MB).'
            );
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new \InvalidArgumentException("{$label} upload was invalid.");
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        if (!is_string($mime) || !isset($mimeToExt[$mime])) {
            throw new \InvalidArgumentException("{$label} type not allowed.");
        }

        return ['tmp' => $tmp, 'ext' => $mimeToExt[$mime]];
    }

    /** Creates nested upload dirs if PHP didn’t make them yet. */
    private function ensureDir(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Could not create directory: ' . $dir);
        }
    }
}
