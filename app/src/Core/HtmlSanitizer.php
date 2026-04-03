<?php

declare(strict_types=1);

namespace App\Core;

// Strips bad tags from TinyMCE output (composer package htmlpurifier).
final class HtmlSanitizer
{
    public const HOME_HTML_FIELDS = ['hero_subtitle', 'welcome_p1', 'welcome_p2', 'about_text'];

    private static ?\HTMLPurifier $purifier = null;

    public static function purify(string $dirty): string
    {
        return self::purifier()->purify($dirty);
    }

    /** True if there is no real text (only empty tags / spaces). */
    public static function isEmptyHtml(string $html): bool
    {
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));

        return $plain === '';
    }

    private static function purifier(): \HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = \HTMLPurifier_Config::createDefault();
        // Default cache lives under vendor/ (often not writable in Docker) — use app storage instead.
        $cacheDir = dirname(__DIR__, 2) . '/storage/htmlpurifier';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        if (is_writable($cacheDir)) {
            $config->set('Cache.SerializerPath', $cacheDir);
        } else {
            $config->set('Cache.DefinitionImpl', false);
        }
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,a[href|target|rel|title],ul,ol,li,span');
        // Reject pasted inline colours/fonts from Word/TinyMCE (they break dark theme).
        $config->set('CSS.AllowedProperties', []);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);

        self::$purifier = new \HTMLPurifier($config);

        return self::$purifier;
    }
}
