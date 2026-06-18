<?php

declare(strict_types=1);

namespace App\Services;

use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// qr png data uris for embedding in ticket pdfs
final class QrCodeService
{
    // base64 png for embedding in ticket pdf
    public function pngDataUri(string $text): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 5,
        ]);

        return (new QRCode($options))->render($text);
    }

    // one data uri per ticket code
    public function pngDataUrisForCodes(array $codes): array
    {
        $uris = [];
        foreach ($codes as $code) {
            if ($code === '') {
                continue;
            }
            $uris[$code] = $this->pngDataUri($code);
        }

        return $uris;
    }
}
