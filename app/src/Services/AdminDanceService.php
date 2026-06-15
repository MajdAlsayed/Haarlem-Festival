<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServiceInterface\AdminDanceServiceInterface;
use App\Core\HtmlSanitizer;
use App\Exceptions\ValidationException;
use App\Repositories\DanceCmsRepository;
use App\Repositories\DanceSettingsRepository;
use App\Repositories\JazzCmsRepository;
use App\Repositories\SettingsRepository;
use App\ViewModels\AdminDanceEditViewModel;

// all the brains for the dance admin: events, homepage artists, and the cms settings form
class AdminDanceService implements AdminDanceServiceInterface
{
    public function __construct(
        private DanceCmsRepository $danceCms,
        private DanceSettingsRepository $danceSettingsRepository,
        private SettingsRepository $settingsRepository,
        private JazzCmsRepository $jazzCms
    ) {
    }

    // global site settings the admin layout needs (header/nav)
    public function appSettings(): array
    {
        return $this->settingsRepository->getAll();
    }

    // —— dance events ——————————————————————————————————————————————————————

    public function listEventsForAdmin(): array
    {
        return $this->danceCms->listDanceEventsForAdmin();
    }

    public function listVenues(): array
    {
        return $this->danceCms->listVenues();
    }

    public function getEventForEdit(int $eventId): ?array
    {
        return $this->danceCms->getDanceEventById($eventId);
    }

    public function getEventAudio(int $eventId): ?array
    {
        return $this->jazzCms->getEventAudio($eventId);
    }

    // validate + save a dance event (create or update) and return its id
    public function saveEvent(array $post): int
    {
        $eventId = isset($post['event_id']) ? (int) $post['event_id'] : 0;
        $venueId = (int) ($post['venue_id'] ?? 0);
        $title = trim((string) ($post['title'] ?? ''));
        $description = trim((string) ($post['description'] ?? ''));
        $eventDay = trim((string) ($post['event_day'] ?? 'friday'));
        $startTime = trim((string) ($post['start_time'] ?? ''));
        $endTime = trim((string) ($post['end_time'] ?? ''));
        $hall = trim((string) ($post['hall'] ?? ''));
        $seatsRaw = trim((string) ($post['seats'] ?? ''));
        $seats = $seatsRaw === '' ? null : (int) $seatsRaw;
        $priceRaw = trim((string) ($post['price'] ?? ''));
        $price = $priceRaw === '' ? null : $priceRaw;

        if ($title === '' || $venueId <= 0 || $startTime === '') {
            throw new ValidationException('Title, venue, and start time are required.');
        }

        try {
            if ($eventId > 0) {
                $this->danceCms->updateDanceEvent(
                    $eventId,
                    $venueId,
                    $title,
                    $description,
                    $eventDay,
                    $startTime,
                    $endTime !== '' ? $endTime : null,
                    $hall !== '' ? $hall : null,
                    $seats,
                    $price
                );
                $newId = $eventId;
            } else {
                $newId = $this->danceCms->createDanceEvent(
                    $venueId,
                    $title,
                    $description,
                    $eventDay,
                    $startTime,
                    $endTime !== '' ? $endTime : null,
                    $hall !== '' ? $hall : null,
                    $seats,
                    $price
                );
            }

            $this->saveEventAudio($newId, $post);
        } catch (\Throwable $e) {
            throw new ValidationException('Could not save event: ' . $e->getMessage());
        }

        return $newId;
    }

    // store/clear the optional preview audio for an event
    private function saveEventAudio(int $eventId, array $post): void
    {
        $audioPath = trim((string) ($post['preview_audio_path'] ?? ''));
        $audioTitle = trim((string) ($post['preview_audio_title'] ?? ''));
        $clearAudio = !empty($post['clear_preview_audio']);

        if ($clearAudio) {
            $this->jazzCms->deleteEventAudio($eventId);
        } elseif ($audioPath !== '') {
            $this->jazzCms->upsertEventAudio($eventId, $audioPath, $audioTitle !== '' ? $audioTitle : null);
        }
    }

    public function deleteEvent(int $eventId): void
    {
        $this->danceCms->deleteDanceEvent($eventId);
    }

    // —— homepage artists (stored as json in dance_settings['artists']) ——————

    public function listArtists(): array
    {
        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $artists = $merged['artists'] ?? [];

        return is_array($artists) ? $artists : [];
    }

    public function getArtistForEdit(string $slug): ?array
    {
        foreach ($this->listArtists() as $artist) {
            if (is_array($artist) && isset($artist['slug']) && (string) $artist['slug'] === $slug) {
                return [
                    'name' => (string) ($artist['name'] ?? ''),
                    'slug' => (string) ($artist['slug'] ?? ''),
                    'bio' => (string) ($artist['bio'] ?? ''),
                    'image' => (string) ($artist['image'] ?? ''),
                ];
            }
        }

        return null;
    }

    // validate + save one homepage artist card into the json list
    public function saveArtist(array $post): void
    {
        $originalSlug = trim((string) ($post['original_slug'] ?? ''));
        $name = trim((string) ($post['name'] ?? ''));
        $slug = trim((string) ($post['slug'] ?? ''));
        $bio = trim((string) ($post['bio'] ?? ''));
        $image = trim((string) ($post['image'] ?? ''));

        if ($name === '' || $slug === '' || !$this->isValidArtistSlug($slug)) {
            throw new ValidationException('Name and a valid slug (lowercase letters, numbers, hyphens) are required.');
        }
        if ($image !== '' && $this->looksUnsafeFilename($image)) {
            throw new ValidationException('Image: filename only, no path characters.');
        }

        // keep every other artist, and make sure the new slug isn't already taken
        $list = [];
        foreach ($this->listArtists() as $artist) {
            if (!is_array($artist) || empty($artist['slug'])) {
                continue;
            }
            $existingSlug = (string) $artist['slug'];
            if ($originalSlug !== '' && $existingSlug === $originalSlug) {
                continue;
            }
            if ($existingSlug === $slug) {
                throw new ValidationException('That slug is already used.');
            }
            $list[] = [
                'name' => (string) ($artist['name'] ?? ''),
                'slug' => $existingSlug,
                'bio' => (string) ($artist['bio'] ?? ''),
                'image' => (string) ($artist['image'] ?? ''),
            ];
        }

        $list[] = [
            'name' => $name,
            'slug' => $slug,
            'bio' => $bio,
            'image' => $image,
        ];

        $this->danceSettingsRepository->upsertSetting('artists', json_encode(array_values($list)));
    }

    public function deleteArtist(string $slug): void
    {
        if (!$this->isValidArtistSlug($slug)) {
            throw new ValidationException('Invalid artist.');
        }

        $list = [];
        foreach ($this->listArtists() as $artist) {
            if (!is_array($artist) || empty($artist['slug'])) {
                continue;
            }
            if ((string) $artist['slug'] === $slug) {
                continue;
            }
            $list[] = [
                'name' => (string) ($artist['name'] ?? ''),
                'slug' => (string) ($artist['slug'] ?? ''),
                'bio' => (string) ($artist['bio'] ?? ''),
                'image' => (string) ($artist['image'] ?? ''),
            ];
        }

        $this->danceSettingsRepository->upsertSetting('artists', json_encode(array_values($list)));
    }

    // —— cms settings form ————————————————————————————————————————————————

    // build the big edit form view model from merged settings + config defaults
    public function buildEditViewModel(string $csrf, string $uploadCsrf, ?string $error, ?string $success): AdminDanceEditViewModel
    {
        $merged = $this->danceSettingsRepository->getMergedWithConfig();
        $defaults = require __DIR__ . '/../Config/dance.php';

        $paragraphs = $this->settingArray($merged, 'about_paragraphs', []);
        $mapPair = $this->settingArray($merged, 'default_map_coordinates', $defaults['default_map_coordinates'] ?? [52.3813, 4.6368]);
        if (count($mapPair) < 2) {
            $mapPair = [52.3813, 4.6368];
        }

        return new AdminDanceEditViewModel(
            csrf: $csrf,
            dancePageTitle: $this->settingText($merged, 'dance_page_title', 'Dance Festival'),
            aboutSectionHeading: $this->settingText($merged, 'about_section_heading', (string) ($defaults['about_section_heading'] ?? 'About Dance')),
            featuredSectionTitle: $this->settingText($merged, 'featured_section_title', (string) ($defaults['featured_section_title'] ?? 'Featured Events')),
            allEventsSectionTitle: $this->settingText($merged, 'all_events_section_title', (string) ($defaults['all_events_section_title'] ?? 'All Events')),
            artistsSectionTitle: $this->settingText($merged, 'artists_section_title', (string) ($defaults['artists_section_title'] ?? 'Artist(s)')),
            heroCtaLabel: $this->settingText($merged, 'hero_cta_label', (string) ($defaults['hero_cta_label'] ?? 'View Dance Events')),
            heroImage: $this->settingText($merged, 'hero_image', ''),
            heroSubtitle: $this->settingText($merged, 'hero_subtitle', ''),
            aboutP1: isset($paragraphs[0]) && is_string($paragraphs[0]) ? $paragraphs[0] : '',
            aboutP2: isset($paragraphs[1]) && is_string($paragraphs[1]) ? $paragraphs[1] : '',
            aboutP3: isset($paragraphs[2]) && is_string($paragraphs[2]) ? $paragraphs[2] : '',
            featuredImagesLines: $this->arrayToLines($this->settingArray($merged, 'featured_images', $defaults['featured_images'] ?? [])),
            fridayImagesLines: $this->arrayToLines($this->settingArray($merged, 'friday_images', $defaults['friday_images'] ?? [])),
            saturdayImagesLines: $this->arrayToLines($this->settingArray($merged, 'saturday_images', $defaults['saturday_images'] ?? [])),
            sundayImagesLines: $this->arrayToLines($this->settingArray($merged, 'sunday_images', $defaults['sunday_images'] ?? [])),
            featuredGenreLabelsLines: $this->arrayToLines($this->settingArray($merged, 'featured_genre_labels', $defaults['featured_genre_labels'] ?? [])),
            breadcrumbHomeLabel: $this->settingText($merged, 'breadcrumb_home_label', (string) ($defaults['breadcrumb_home_label'] ?? 'HOME')),
            breadcrumbDanceLabel: $this->settingText($merged, 'breadcrumb_dance_label', (string) ($defaults['breadcrumb_dance_label'] ?? 'DANCE')),
            eventDetailListPath: $this->settingText($merged, 'event_detail_list_path', (string) ($defaults['event_detail_list_path'] ?? '/dance')),
            eventDetailPhotosContext: $this->settingText($merged, 'event_detail_photos_context', (string) ($defaults['event_detail_photos_context'] ?? 'dance_event_detail')),
            eventDetailHeroFallback: $this->settingText($merged, 'event_detail_hero_fallback', (string) ($defaults['event_detail_hero_fallback'] ?? '')),
            eventDetailGalleryLines: $this->arrayToLines($this->settingArray($merged, 'event_detail_gallery_fallbacks', $defaults['event_detail_gallery_fallbacks'] ?? [])),
            defaultEventDay: $this->settingText($merged, 'default_event_day', (string) ($defaults['default_event_day'] ?? 'friday')),
            eventDetailVenueCountry: $this->settingText($merged, 'event_detail_venue_country', (string) ($defaults['event_detail_venue_country'] ?? 'Netherlands')),
            defaultMapLat: (string) $mapPair[0],
            defaultMapLon: (string) $mapPair[1],
            venueCoordinatesJson: $this->jsonOrDefault($this->settingArray($merged, 'venue_coordinates', $defaults['venue_coordinates'] ?? []), '{}'),
            artistDetailDanceImagesBasePath: $this->settingText($merged, 'dance_images_base_path', (string) ($defaults['dance_images_base_path'] ?? '/images/dance/')),
            artistDetailPhotosContextHero: $this->settingText($merged, 'artist_detail_photos_context_hero', (string) ($defaults['artist_detail_photos_context_hero'] ?? 'dance_artist_hero')),
            artistDetailPhotosContextSchedule: $this->settingText($merged, 'artist_detail_photos_context_schedule', (string) ($defaults['artist_detail_photos_context_schedule'] ?? 'dance_artist_schedule')),
            artistDetailPhotosContextMusic: $this->settingText($merged, 'artist_detail_photos_context_music', (string) ($defaults['artist_detail_photos_context_music'] ?? 'dance_artist_music')),
            artistDetailHeroFallback: $this->settingText($merged, 'artist_detail_hero_fallback', (string) ($defaults['artist_detail_hero_fallback'] ?? '')),
            artistDetailScheduleFallbacksJson: $this->jsonOrDefault($this->settingArray($merged, 'artist_detail_schedule_fallbacks', $defaults['artist_detail_schedule_fallbacks'] ?? []), '{}'),
            artistDetailMusicProfileSlotsJson: $this->jsonOrDefault($this->settingArray($merged, 'artist_detail_music_profile_slots', $defaults['artist_detail_music_profile_slots'] ?? []), '{}'),
            artistDetailMusicAlbumSlotsJson: $this->jsonOrDefault($this->settingArray($merged, 'artist_detail_music_album_slots', $defaults['artist_detail_music_album_slots'] ?? []), '{}'),
            artistDetailMusicProfileFallback: $this->settingText($merged, 'artist_detail_music_profile_fallback', (string) ($defaults['artist_detail_music_profile_fallback'] ?? '')),
            artistDetailMusicAlbumFallback: $this->settingText($merged, 'artist_detail_music_album_fallback', (string) ($defaults['artist_detail_music_album_fallback'] ?? '')),
            artistDetailDefaultLocation: $this->settingText($merged, 'artist_detail_default_location', (string) ($defaults['artist_detail_default_location'] ?? 'Netherlands')),
            artistDetailDefaultAlbumTitle: $this->settingText($merged, 'artist_detail_default_album_title', (string) ($defaults['artist_detail_default_album_title'] ?? 'Featured')),
            artistDetailDefaultAlbumSub: $this->settingText($merged, 'artist_detail_default_album_sub', (string) ($defaults['artist_detail_default_album_sub'] ?? 'Album')),
            artistDetailGalleryTargetCount: (string) ($merged['artist_detail_gallery_target_count'] ?? $defaults['artist_detail_gallery_target_count'] ?? 4),
            artistDetailHeroTaglineMaxChars: (string) ($merged['artist_detail_hero_tagline_max_chars'] ?? $defaults['artist_detail_hero_tagline_max_chars'] ?? 160),
            artistDetailGalleryStatsJson: $this->jsonOrDefault($this->settingArray($merged, 'artist_detail_gallery_stats_fallback', $defaults['artist_detail_gallery_stats_fallback'] ?? []), '[]'),
            uploadCsrf: $uploadCsrf,
            appSettings: $this->settingsRepository->getAll(),
            error: $error,
            success: $success
        );
    }

    // validate each section of the form, then write it all to dance_settings in one go
    public function saveSettings(array $post): void
    {
        $defaults = require __DIR__ . '/../Config/dance.php';

        $settings = array_merge(
            $this->validatePageCopy($post, $defaults),
            $this->validateEventDetail($post, $defaults),
            $this->validateArtistDetail($post)
        );

        foreach ($settings as $key => $value) {
            $this->danceSettingsRepository->upsertSetting($key, $value);
        }
    }

    // section 1: page title, hero, about text, day images — returns setting_key => value
    /** @return array<string, string> */
    private function validatePageCopy(array $post, array $defaults): array
    {
        $pageTitle = trim((string) ($post['dance_page_title'] ?? ''));
        $aboutSectionHeading = trim((string) ($post['about_section_heading'] ?? ''));
        $featuredSectionTitle = trim((string) ($post['featured_section_title'] ?? ''));
        $allEventsSectionTitle = trim((string) ($post['all_events_section_title'] ?? ''));
        $artistsSectionTitle = trim((string) ($post['artists_section_title'] ?? ''));
        $heroCtaLabel = trim((string) ($post['hero_cta_label'] ?? ''));
        $heroImage = trim((string) ($post['hero_image'] ?? ''));
        // rich text goes through htmlpurifier so admins can't paste script tags
        $heroSubtitle = HtmlSanitizer::purify((string) ($post['hero_subtitle'] ?? ''));
        $p1 = HtmlSanitizer::purify((string) ($post['about_p1'] ?? ''));
        $p2 = HtmlSanitizer::purify((string) ($post['about_p2'] ?? ''));
        $p3 = HtmlSanitizer::purify((string) ($post['about_p3'] ?? ''));

        if ($pageTitle === '' || $heroImage === '') {
            throw new ValidationException('Title and hero image name required.');
        }
        if ($this->looksUnsafeFilename($heroImage)) {
            throw new ValidationException('Hero image: filename only, no / or ..');
        }
        if (HtmlSanitizer::isEmptyHtml($heroSubtitle)) {
            throw new ValidationException('Hero subtitle cannot be empty.');
        }
        if (strlen($heroSubtitle) > 100000) {
            throw new ValidationException('Hero subtitle is too long.');
        }
        if ($aboutSectionHeading === '' || $featuredSectionTitle === '' || $allEventsSectionTitle === '' || $artistsSectionTitle === '' || $heroCtaLabel === '') {
            throw new ValidationException('Section titles / button label empty.');
        }

        $about = array_values(array_filter([$p1, $p2, $p3], static fn ($s) => is_string($s) && !HtmlSanitizer::isEmptyHtml($s)));
        if ($about === []) {
            throw new ValidationException('Need at least one about paragraph.');
        }
        foreach ($about as $block) {
            if (strlen($block) > 100000) {
                throw new ValidationException('An about paragraph is too long.');
            }
        }

        $featured = $this->imagesOrDefault($post, 'featured_images_lines', $defaults, 'featured_images');
        $friday = $this->imagesOrDefault($post, 'friday_images_lines', $defaults, 'friday_images');
        $saturday = $this->imagesOrDefault($post, 'saturday_images_lines', $defaults, 'saturday_images');
        $sunday = $this->imagesOrDefault($post, 'sunday_images_lines', $defaults, 'sunday_images');
        $genreLabels = $this->imagesOrDefault($post, 'featured_genre_labels_lines', $defaults, 'featured_genre_labels');

        foreach ([
            'Featured card images' => $featured,
            'Friday images' => $friday,
            'Saturday images' => $saturday,
            'Sunday images' => $sunday,
        ] as $label => $images) {
            foreach ($images as $filename) {
                if ($this->looksUnsafeFilename($filename)) {
                    throw new ValidationException($label . ': bad filename.');
                }
            }
        }

        return [
            'dance_page_title' => $pageTitle,
            'about_section_heading' => $aboutSectionHeading,
            'featured_section_title' => $featuredSectionTitle,
            'all_events_section_title' => $allEventsSectionTitle,
            'artists_section_title' => $artistsSectionTitle,
            'hero_cta_label' => $heroCtaLabel,
            'hero_image' => $heroImage,
            'hero_subtitle' => $heroSubtitle,
            'about_paragraphs' => json_encode($about),
            'featured_images' => json_encode(array_values($featured)),
            'friday_images' => json_encode(array_values($friday)),
            'saturday_images' => json_encode(array_values($saturday)),
            'sunday_images' => json_encode(array_values($sunday)),
            'featured_genre_labels' => json_encode(array_values($genreLabels)),
        ];
    }

    // section 2: breadcrumbs, list path, map + venue coordinates — returns setting_key => value
    /** @return array<string, string> */
    private function validateEventDetail(array $post, array $defaults): array
    {
        $breadcrumbHome = trim((string) ($post['breadcrumb_home_label'] ?? ''));
        $breadcrumbDance = trim((string) ($post['breadcrumb_dance_label'] ?? ''));
        $eventListPath = trim((string) ($post['event_detail_list_path'] ?? ''));
        $photoCtx = trim((string) ($post['event_detail_photos_context'] ?? ''));
        $heroFb = trim((string) ($post['event_detail_hero_fallback'] ?? ''));
        $galleryLines = $this->linesToStringArray((string) ($post['event_detail_gallery_lines'] ?? ''));
        $defGal = $defaults['event_detail_gallery_fallbacks'] ?? ['DetailsPage/2.png', 'DetailsPage/3.png', 'DetailsPage/4.png'];
        while (count($galleryLines) < 3) {
            $galleryLines[] = $defGal[count($galleryLines)] ?? 'DetailsPage/2.png';
        }
        $galleryLines = array_slice($galleryLines, 0, 3);

        $defaultDay = strtolower(trim((string) ($post['default_event_day'] ?? '')));
        $venueCountry = trim((string) ($post['event_detail_venue_country'] ?? ''));
        $mapLatRaw = trim((string) ($post['default_map_lat'] ?? ''));
        $mapLonRaw = trim((string) ($post['default_map_lon'] ?? ''));
        $venueJsonRaw = (string) ($post['venue_coordinates_json'] ?? '');

        if ($breadcrumbHome === '' || $breadcrumbDance === '') {
            throw new ValidationException('Event detail: breadcrumb labels required.');
        }
        if ($eventListPath === '' || ($eventListPath[0] ?? '') !== '/') {
            throw new ValidationException('Event detail: list path must start with /.');
        }
        if ($photoCtx === '' || !preg_match('/^[a-z0-9_]+$/i', $photoCtx)) {
            throw new ValidationException('Event detail: photos context: letters, numbers, underscores only.');
        }
        if ($heroFb === '' || $this->looksUnsafeRelativePath($heroFb)) {
            throw new ValidationException('Event detail: invalid hero fallback path.');
        }
        foreach ($galleryLines as $filename) {
            if ($this->looksUnsafeRelativePath($filename)) {
                throw new ValidationException('Event detail: invalid gallery fallback path.');
            }
        }
        if ($defaultDay === '' || !preg_match('/^[a-z]+$/', $defaultDay)) {
            throw new ValidationException('Event detail: default day must be a weekday key (e.g. friday).');
        }
        if ($venueCountry === '') {
            throw new ValidationException('Event detail: country label required.');
        }
        if (!is_numeric($mapLatRaw) || !is_numeric($mapLonRaw)) {
            throw new ValidationException('Event detail: default map lat/lon must be numbers.');
        }

        $decodedVc = json_decode($venueJsonRaw, true);
        if (!is_array($decodedVc)) {
            throw new ValidationException('Event detail: venue coordinates must be valid JSON (object: venue name → [lat, lon]).');
        }
        foreach ($decodedVc as $venue => $pair) {
            if (!is_string($venue) || trim($venue) === '') {
                throw new ValidationException('Event detail: each venue name in the map must be a non-empty string.');
            }
            if (!is_array($pair) || !isset($pair[0], $pair[1]) || !is_numeric($pair[0]) || !is_numeric($pair[1])) {
                throw new ValidationException('Event detail: each venue needs [latitude, longitude].');
            }
        }

        return [
            'breadcrumb_home_label' => $breadcrumbHome,
            'breadcrumb_dance_label' => $breadcrumbDance,
            'event_detail_list_path' => $eventListPath,
            'event_detail_photos_context' => $photoCtx,
            'event_detail_hero_fallback' => $heroFb,
            'event_detail_gallery_fallbacks' => json_encode(array_values($galleryLines)),
            'default_event_day' => $defaultDay,
            'event_detail_venue_country' => $venueCountry,
            'default_map_coordinates' => json_encode([(float) $mapLatRaw, (float) $mapLonRaw]),
            'venue_coordinates' => json_encode($decodedVc),
        ];
    }

    // section 3: artist detail images, slots, labels — returns setting_key => value
    /** @return array<string, string> */
    private function validateArtistDetail(array $post): array
    {
        $imgBase = trim((string) ($post['dance_images_base_path'] ?? ''));
        $aHeroCtx = trim((string) ($post['artist_detail_photos_context_hero'] ?? ''));
        $aSchedCtx = trim((string) ($post['artist_detail_photos_context_schedule'] ?? ''));
        $aMusicCtx = trim((string) ($post['artist_detail_photos_context_music'] ?? ''));
        $aHeroFb = trim((string) ($post['artist_detail_hero_fallback'] ?? ''));
        $aSchedJson = (string) ($post['artist_detail_schedule_fallbacks_json'] ?? '');
        $aProfSlotsJson = (string) ($post['artist_detail_music_profile_slots_json'] ?? '');
        $aAlbSlotsJson = (string) ($post['artist_detail_music_album_slots_json'] ?? '');
        $aProfFb = trim((string) ($post['artist_detail_music_profile_fallback'] ?? ''));
        $aAlbFb = trim((string) ($post['artist_detail_music_album_fallback'] ?? ''));
        $aDefLoc = trim((string) ($post['artist_detail_default_location'] ?? ''));
        $aAlbTitle = trim((string) ($post['artist_detail_default_album_title'] ?? ''));
        $aAlbSub = trim((string) ($post['artist_detail_default_album_sub'] ?? ''));
        $aGalTarget = trim((string) ($post['artist_detail_gallery_target_count'] ?? ''));
        $aTagMax = trim((string) ($post['artist_detail_hero_tagline_max_chars'] ?? ''));
        $aGalStatsJson = (string) ($post['artist_detail_gallery_stats_json'] ?? '');

        if ($imgBase === '' || $imgBase[0] !== '/' || !str_ends_with($imgBase, '/')) {
            throw new ValidationException('Artist detail: image base path must start and end with / (e.g. /images/dance/).');
        }
        foreach (['Artist hero context' => $aHeroCtx, 'Artist schedule context' => $aSchedCtx, 'Artist music context' => $aMusicCtx] as $label => $ctx) {
            if ($ctx === '' || !preg_match('/^[a-z0-9_]+$/i', $ctx)) {
                throw new ValidationException($label . ': letters, numbers, underscores only.');
            }
        }
        foreach (['Artist hero fallback' => $aHeroFb, 'Music profile fallback' => $aProfFb, 'Music album fallback' => $aAlbFb] as $label => $path) {
            if ($path === '' || $this->looksUnsafeRelativePath($path)) {
                throw new ValidationException($label . ': invalid path under /images/dance/.');
            }
        }
        if ($aDefLoc === '' || $aAlbTitle === '' || $aAlbSub === '') {
            throw new ValidationException('Artist detail: location / album labels required.');
        }
        if (!ctype_digit($aGalTarget) || (int) $aGalTarget < 1 || (int) $aGalTarget > 20) {
            throw new ValidationException('Artist detail: gallery target count must be 1–20.');
        }
        if (!ctype_digit($aTagMax) || (int) $aTagMax < 40 || (int) $aTagMax > 500) {
            throw new ValidationException('Artist detail: hero tagline max must be 40–500.');
        }

        $decSched = $this->validateScheduleFallbacks($aSchedJson);
        $decProf = $this->validateSlotMap($aProfSlotsJson, 'profile');
        $decAlb = $this->validateSlotMap($aAlbSlotsJson, 'album');
        $decGalStats = $this->validateGalleryStats($aGalStatsJson);

        return [
            'dance_images_base_path' => $imgBase,
            'artist_detail_photos_context_hero' => $aHeroCtx,
            'artist_detail_photos_context_schedule' => $aSchedCtx,
            'artist_detail_photos_context_music' => $aMusicCtx,
            'artist_detail_hero_fallback' => $aHeroFb,
            'artist_detail_schedule_fallbacks' => json_encode($decSched),
            'artist_detail_music_profile_slots' => json_encode($decProf),
            'artist_detail_music_album_slots' => json_encode($decAlb),
            'artist_detail_music_profile_fallback' => $aProfFb,
            'artist_detail_music_album_fallback' => $aAlbFb,
            'artist_detail_default_location' => $aDefLoc,
            'artist_detail_default_album_title' => $aAlbTitle,
            'artist_detail_default_album_sub' => $aAlbSub,
            'artist_detail_gallery_target_count' => $aGalTarget,
            'artist_detail_hero_tagline_max_chars' => $aTagMax,
            'artist_detail_gallery_stats_fallback' => json_encode(array_values($decGalStats)),
        ];
    }

    // decode + check the schedule fallbacks json (slug → path, must include "default")
    /** @return array<string, string> */
    private function validateScheduleFallbacks(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !isset($decoded['default']) || !is_string($decoded['default'])) {
            throw new ValidationException('Artist detail: schedule fallbacks JSON must be an object with a "default" path.');
        }
        foreach ($decoded as $key => $path) {
            if (!is_string($key) || $key === '') {
                throw new ValidationException('Artist detail: schedule fallbacks keys must be non-empty strings (slug or "default").');
            }
            if (!is_string($path) || $this->looksUnsafeRelativePath($path)) {
                throw new ValidationException('Artist detail: schedule fallback path invalid for key "' . $key . '".');
            }
        }

        return $decoded;
    }

    // decode + check a slug → slot-key map (used for both the profile and album rows)
    /** @return array<string, string> */
    private function validateSlotMap(string $json, string $label): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !isset($decoded['default']) || !is_string($decoded['default']) || $decoded['default'] === '') {
            throw new ValidationException('Artist detail: ' . $label . ' slots JSON needs string "default" (Photos slot key).');
        }
        foreach ($decoded as $slotKey => $slotValue) {
            if (!is_string($slotKey) || !is_string($slotValue) || $slotValue === '' || !preg_match('/^[a-z0-9_]+$/i', $slotValue)) {
                throw new ValidationException('Artist detail: ' . $label . ' slots: keys and values must be non-empty; values = slot keys (a-z, 0-9, _).');
            }
        }

        return $decoded;
    }

    // decode + check the gallery stats json (an array of {num, label})
    /** @return array[] */
    private function validateGalleryStats(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new ValidationException('Artist detail: gallery stats must be a JSON array of {num, label}.');
        }
        foreach ($decoded as $index => $row) {
            if (!is_array($row) || !isset($row['num'], $row['label']) || !is_string($row['num']) || !is_string($row['label'])) {
                throw new ValidationException('Artist detail: gallery stats row #' . ($index + 1) . ' needs num + label strings.');
            }
        }

        return $decoded;
    }

    // read image lines from the form, falling back to the config defaults when empty
    /** @return string[] */
    private function imagesOrDefault(array $post, string $field, array $defaults, string $defaultKey): array
    {
        $images = $this->linesToStringArray((string) ($post[$field] ?? ''));
        if ($images === []) {
            $images = $defaults[$defaultKey] ?? [];
        }

        return $images;
    }

    // —— small helpers ————————————————————————————————————————————————————

    // read a text setting, or use the default if it's missing / not a string
    private function settingText(array $settings, string $key, string $default): string
    {
        return isset($settings[$key]) && is_string($settings[$key]) ? $settings[$key] : $default;
    }

    // read an array setting, or use the default if it's missing / not an array
    private function settingArray(array $settings, string $key, array $default): array
    {
        return isset($settings[$key]) && is_array($settings[$key]) ? $settings[$key] : $default;
    }

    // json encode for the form, or a fallback string if encoding fails
    private function jsonOrDefault(array $value, string $fallback): string
    {
        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $json !== false ? $json : $fallback;
    }

    // strict slug rule for url-safe artist links
    private function isValidArtistSlug(string $slug): bool
    {
        return (bool) preg_match('/^[a-z0-9-]+$/', $slug);
    }

    // split textarea lines into a clean string array (trim + drop empty)
    /** @return string[] */
    private function linesToStringArray(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed !== '') {
                $out[] = $trimmed;
            }
        }

        return $out;
    }

    // join a string array back into textarea lines for the form
    private function arrayToLines(array $items): string
    {
        $lines = [];
        foreach ($items as $item) {
            if (is_string($item) && trim($item) !== '') {
                $lines[] = trim($item);
            }
        }

        return implode("\n", $lines);
    }

    // reject filename-only fields that try path traversal or folders
    private function looksUnsafeFilename(string $name): bool
    {
        return str_contains($name, '..') || str_contains($name, '/') || str_contains($name, '\\');
    }

    // relative path under /public/images/dance/ (blocks .. and absolute paths)
    private function looksUnsafeRelativePath(string $path): bool
    {
        if ($path === '' || str_contains($path, '..')) {
            return true;
        }

        return str_starts_with($path, '/') || str_starts_with($path, '\\');
    }
}
