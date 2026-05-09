<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AdminAuth;
use App\Core\Csrf;
use App\Core\HtmlSanitizer;
use App\Repositories\PageRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\AdminHomepageEditViewModel;

/**
 * CMS: edit the public homepage title (pages.slug = home) and WYSIWYG/plain fields under `cms_home_*` in site_settings.
 * Rich HTML fields go through HtmlSanitizer; hrefs and image paths are validated to block javascript: and path traversal.
 */
final class AdminHomepageController
{
    private PageRepository $pageRepository;
    private SettingsRepository $settingsRepository;

    public function __construct()
    {
        $this->pageRepository = new PageRepository();
        $this->settingsRepository = new SettingsRepository();
    }

    /** GET (or re-display after validation error): show HomepageEdit with merged CMS values and CSRF tokens. */
    public function showForm(?string $error = null, ?string $success = null): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        $page = $this->pageRepository->findBySlugForAdmin('home');
        $title = $page !== null ? $page->title : '';

        $cmsHome = $this->settingsRepository->getMergedCmsHome();

        $viewModel = new AdminHomepageEditViewModel(
            csrf: Csrf::token('cms_homepage'),
            pageTitle: $title,
            cmsHome: $cmsHome,
            uploadCsrf: Csrf::token('cms_upload'),
            appSettings: $this->settingsRepository->getAll(),
            error: $error,
            success: $success
        );

        require __DIR__ . '/../Views/Admin/HomepageEdit.php';
    }

    /** POST: validate CSRF, save page title, then each cms_home field (purify HTML where configured, upsert into settings). */
    public function save(): void
    {
        if (!AdminAuth::requireAdmin()) {
            return;
        }

        if (!Csrf::validate('cms_homepage', $_POST['_csrf'] ?? null)) {
            $this->showForm('Wrong CSRF token.', null);

            return;
        }

        $raw = trim((string) ($_POST['page_title'] ?? ''));
        if ($raw === '') {
            $this->showForm('Title cannot be empty.', null);

            return;
        }

        $len = function_exists('mb_strlen') ? mb_strlen($raw, 'UTF-8') : strlen($raw);
        if ($len > 255) {
            $this->showForm('Title must be at most 255 characters.', null);

            return;
        }

        $ok = $this->pageRepository->updateTitleBySlug('home', $raw);
        if (!$ok) {
            $this->showForm('Could not save title (home page missing in DB?).', null);

            return;
        }

        $app = require __DIR__ . '/../Config/app.php';
        $cmsKeys = array_keys($app['cms_home'] ?? []);
        $posted = $_POST['cms'] ?? null;
        if (!is_array($posted)) {
            $this->showForm('Bad form data.', null);

            return;
        }

        foreach ($cmsKeys as $subKey) {
            $raw = isset($posted[$subKey]) ? (string) $posted[$subKey] : '';
            $val = in_array($subKey, HtmlSanitizer::HOME_HTML_FIELDS, true)
                ? HtmlSanitizer::purify($raw)
                : trim($raw);
            $err = self::validateCmsField($subKey, $val);
            if ($err !== null) {
                $this->showForm($err, null);

                return;
            }
            $dbKey = 'cms_home_' . $subKey;
            if (!$this->settingsRepository->upsertSetting($dbKey, $val)) {
                $this->showForm('DB save failed.', null);

                return;
            }
        }

        $this->showForm(null, 'Saved.');
    }

    private static function validateCmsField(string $key, string $val): ?string
    {
        $maxShort = 500;
        $maxRich = 100000;
        $maxHref = 500;

        $richHtmlKeys = HtmlSanitizer::HOME_HTML_FIELDS;
        $hrefFields = ['hero_cta_href', 'about_more_href'];
        $pathFields = ['about_image_src'];

        $max = in_array($key, $richHtmlKeys, true) ? $maxRich : (in_array($key, $hrefFields, true) ? $maxHref : $maxShort);
        $len = function_exists('mb_strlen') ? mb_strlen($val, 'UTF-8') : strlen($val);
        if ($len > $max) {
            return 'Field "' . $key . '" is too long (max ' . $max . ' characters).';
        }

        $requiredPlain = [
            'hero_heading', 'hero_cta_label', 'welcome_heading', 'about_heading', 'about_image_alt', 'about_more_label',
            'events_heading', 'events_subtitle', 'events_info_label', 'events_tickets_label',
            'events_category_dance', 'events_category_jazz', 'events_category_history', 'events_category_yammy', 'events_category_stories',
            'expect_heading', 'expect_intro', 'expect_subheading', 'expect_cta'
        ];
        if (in_array($key, $requiredPlain, true) && $val === '') {
            return 'Fill in headings, button label and image alt.';
        }

        $requiredRich = ['hero_subtitle', 'welcome_p1', 'welcome_p2', 'about_text'];
        if (in_array($key, $requiredRich, true) && HtmlSanitizer::isEmptyHtml($val)) {
            return 'Fill in hero subtitle, welcome text and about text.';
        }

        if (in_array($key, $pathFields, true)) {
            if ($val === '') {
                return 'About image path cannot be empty.';
            }
            if (!str_starts_with($val, '/') || str_contains($val, '..')) {
                return 'About image must be a site path starting with / (no ..).';
            }
        }

        if (in_array($key, $hrefFields, true)) {
            if ($val === '') {
                return 'Link fields cannot be empty (use # for no target).';
            }
            if (self::looksUnsafeHref($val)) {
                return 'Bad link in ' . $key;
            }
        }

        if ($key === 'expect_cards_json') {
            $decoded = json_decode($val, true);
            if (!is_array($decoded)) {
                return 'Expect cards must be valid JSON array.';
            }
        }

        return null;
    }

    /** Allows #fragment, same-site paths, or http(s) URLs; rejects everything else and `..`. */
    private static function looksUnsafeHref(string $href): bool
    {
        $t = trim($href);
        if ($t === '' || str_contains($t, "\n") || str_contains($t, "\r")) {
            return true;
        }
        if (str_starts_with($t, '#')) {
            return str_contains($t, '..');
        }
        if (str_starts_with($t, '/')) {
            return str_contains($t, '..');
        }
        if (preg_match('#^https?://#i', $t)) {
            return str_contains($t, '..');
        }

        return true;
    }
}
