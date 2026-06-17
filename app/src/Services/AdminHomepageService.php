<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\AdminHomepageServiceInterface;
use App\Contracts\ServiceInterface\PageServiceInterface;
use App\Contracts\ServiceInterface\SettingsServiceInterface;
use App\Core\HtmlSanitizer;
use App\Exceptions\ValidationException;
use App\ViewModels\AdminHomepageEditViewModel;

// load + save the homepage cms (page title + the cms_home_* settings)
class AdminHomepageService implements AdminHomepageServiceInterface
{
    private const MAX_TITLE = 255;
    private const MAX_SHORT = 500;
    private const MAX_RICH = 100000;
    private const MAX_HREF = 500;

    public function __construct(
        private PageServiceInterface $pageService,
        private SettingsServiceInterface $settingsService,
    ) {
    }

    // build the edit form view model from the home page row + merged cms values
    public function buildEditViewModel(string $csrf, string $uploadCsrf, ?string $error, ?string $success): AdminHomepageEditViewModel
    {
        $page = $this->pageService->findBySlugForAdmin('home');
        $title = $page !== null ? $page->title : '';

        return new AdminHomepageEditViewModel(
            csrf: $csrf,
            pageTitle: $title,
            cmsHome: $this->settingsService->getMergedCmsHome(),
            uploadCsrf: $uploadCsrf,
            appSettings: $this->settingsService->getAll(),
            error: $error,
            success: $success
        );
    }

    // validate + save the page title and every cms_home field (throws ValidationException on bad input)
    public function saveSettings(array $post): void
    {
        $this->saveTitle($post);
        $this->saveCmsFields($post);
    }

    // the home page title lives on the pages row, not in settings
    private function saveTitle(array $post): void
    {
        $title = trim((string) ($post['page_title'] ?? ''));
        if ($title === '') {
            throw new ValidationException('Title cannot be empty.');
        }
        if ($this->length($title) > self::MAX_TITLE) {
            throw new ValidationException('Title must be at most 255 characters.');
        }
        if (!$this->pageService->updateTitleBySlug('home', $title)) {
            throw new ValidationException('Could not save title (home page missing in DB?).');
        }
    }

    // every cms_home_* field: purify the html ones, validate, then save
    private function saveCmsFields(array $post): void
    {
        $posted = $post['cms'] ?? null;
        if (!is_array($posted)) {
            throw new ValidationException('Bad form data.');
        }

        $appConfig = require __DIR__ . '/../Config/app.php';
        $cmsKeys = array_keys($appConfig['cms_home'] ?? []);

        foreach ($cmsKeys as $key) {
            $raw = isset($posted[$key]) ? (string) $posted[$key] : '';
            $value = in_array($key, HtmlSanitizer::HOME_HTML_FIELDS, true)
                ? HtmlSanitizer::purify($raw)
                : trim($raw);

            $this->validateCmsField($key, $value);

            if (!$this->settingsService->upsertSetting('cms_home_' . $key, $value)) {
                throw new ValidationException('DB save failed.');
            }
        }
    }

    // check one cms_home field by its name; throws ValidationException with a friendly message
    private function validateCmsField(string $key, string $value): void
    {
        $this->checkLength($key, $value);
        $this->checkRequired($key, $value);
        $this->checkImagePath($key, $value);
        $this->checkHref($key, $value);
        $this->checkExpectCards($key, $value);
    }

    private function checkLength(string $key, string $value): void
    {
        $max = self::MAX_SHORT;
        // rich text and the expect-cards json blob can be much longer than a normal field
        if (in_array($key, HtmlSanitizer::HOME_HTML_FIELDS, true) || $key === 'expect_cards_json') {
            $max = self::MAX_RICH;
        } elseif (in_array($key, $this->hrefFields(), true)) {
            $max = self::MAX_HREF;
        }

        if ($this->length($value) > $max) {
            throw new ValidationException('Field "' . $key . '" is too long (max ' . $max . ' characters).');
        }
    }

    private function checkRequired(string $key, string $value): void
    {
        $requiredPlain = [
            'hero_heading', 'hero_cta_label', 'welcome_heading', 'about_heading', 'about_image_alt', 'about_more_label',
            'events_heading', 'events_subtitle', 'events_info_label', 'events_tickets_label',
            'events_category_dance', 'events_category_jazz', 'events_category_history', 'events_category_yammy', 'events_category_stories',
            'expect_heading', 'expect_intro', 'expect_subheading', 'expect_cta',
        ];
        if (in_array($key, $requiredPlain, true) && $value === '') {
            throw new ValidationException('Fill in headings, button label and image alt.');
        }

        $requiredRich = ['hero_subtitle', 'welcome_p1', 'welcome_p2', 'about_text'];
        if (in_array($key, $requiredRich, true) && HtmlSanitizer::isEmptyHtml($value)) {
            throw new ValidationException('Fill in hero subtitle, welcome text and about text.');
        }
    }

    private function checkImagePath(string $key, string $value): void
    {
        if ($key !== 'about_image_src') {
            return;
        }
        if ($value === '') {
            throw new ValidationException('About image path cannot be empty.');
        }
        if (!str_starts_with($value, '/') || str_contains($value, '..')) {
            throw new ValidationException('About image must be a site path starting with / (no ..).');
        }
    }

    private function checkHref(string $key, string $value): void
    {
        if (!in_array($key, $this->hrefFields(), true)) {
            return;
        }
        if ($value === '') {
            throw new ValidationException('Link fields cannot be empty (use # for no target).');
        }
        if ($this->looksUnsafeHref($value)) {
            throw new ValidationException('Bad link in ' . $key);
        }
    }

    private function checkExpectCards(string $key, string $value): void
    {
        if ($key !== 'expect_cards_json') {
            return;
        }
        if (!is_array(json_decode($value, true))) {
            throw new ValidationException('Expect cards must be valid JSON array.');
        }
    }

    private function hrefFields(): array
    {
        return ['hero_cta_href', 'about_more_href'];
    }

    // allow #fragment, same-site paths, or http(s) urls; reject everything else and ".."
    private function looksUnsafeHref(string $href): bool
    {
        $href = trim($href);
        if ($href === '' || str_contains($href, "\n") || str_contains($href, "\r")) {
            return true;
        }
        if (str_starts_with($href, '#') || str_starts_with($href, '/') || preg_match('#^https?://#i', $href)) {
            return str_contains($href, '..');
        }

        return true;
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }
}
