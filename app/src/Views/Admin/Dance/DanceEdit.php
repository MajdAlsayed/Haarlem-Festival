<?php
/** Admin form to edit Dance page content; submits to AdminDanceController::save. Uses shared admin.css like Jazz/Food. */
/** @var \App\ViewModels\AdminDanceEditViewModel $viewModel */
$app = $viewModel->appSettings;
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$pageTitle = 'Edit Dance page — ' . ($app['site_name'] ?? 'Haarlem Festival');
$pageStyles = ['/css/admin.css'];
$bodyClass = 'admin-page';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/../../partials/head.php'; ?>
<body class="<?= $h($bodyClass) ?>"
      data-upload-csrf="<?= $h($viewModel->uploadCsrf) ?>"
      data-upload-url="/admin/cms/upload">

<?php require __DIR__ . '/../../partials/header.php'; ?>

<main class="admin-main">
    <div class="admin-container admin-container--wide">

        <nav class="admin-breadcrumb">
            <a href="/"><?= $h($app['site_name'] ?? 'Festival') ?></a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin">Admin</a>
            <span class="admin-breadcrumb-sep">›</span>
            <a href="/admin/dance">Dance</a>
            <span class="admin-breadcrumb-sep">›</span>
            <span>Edit page</span>
        </nav>

        <h1 class="admin-title">Dance page copy</h1>
        <p class="admin-lead">Data in <code>dance_settings</code>, defaults in <code>dance.php</code>. TinyMCE on hero + about. Upload fills hero filename. <a href="/dance" target="_blank" rel="noopener">View public page</a></p>

        <?php if ($viewModel->success !== null): ?>
            <div class="admin-alert admin-alert-success"><?= $h($viewModel->success) ?></div>
        <?php endif; ?>
        <?php if ($viewModel->error !== null): ?>
            <div class="admin-alert admin-alert-error"><?= $h($viewModel->error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/cms/dance" class="admin-form admin-form--wide">
            <input type="hidden" name="_csrf" value="<?= $h($viewModel->csrf) ?>">

            <div class="admin-field">
                <label for="dance_page_title">Page title (browser tab + hero H1)</label>
                <input type="text" id="dance_page_title" name="dance_page_title" required maxlength="120"
                       value="<?= $h($viewModel->dancePageTitle) ?>" class="admin-input">
            </div>

            <fieldset class="admin-fieldset">
                <legend>Section headings</legend>
                <div class="admin-field">
                    <label for="about_section_heading">About block heading</label>
                    <input type="text" id="about_section_heading" name="about_section_heading" required maxlength="120"
                           value="<?= $h($viewModel->aboutSectionHeading) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="featured_section_title">Featured events title</label>
                    <input type="text" id="featured_section_title" name="featured_section_title" required maxlength="120"
                           value="<?= $h($viewModel->featuredSectionTitle) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="all_events_section_title">All events title</label>
                    <input type="text" id="all_events_section_title" name="all_events_section_title" required maxlength="120"
                           value="<?= $h($viewModel->allEventsSectionTitle) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="artists_section_title">Artists section title</label>
                    <input type="text" id="artists_section_title" name="artists_section_title" required maxlength="120"
                           value="<?= $h($viewModel->artistsSectionTitle) ?>" class="admin-input">
                </div>
            </fieldset>

            <div class="admin-field">
                <label for="hero_image">Hero background image filename</label>
                <input type="text" id="hero_image" name="hero_image" required maxlength="255"
                       value="<?= $h($viewModel->heroImage) ?>" class="admin-input">
                <small class="admin-hint">File under <code>/public/images/dance/</code> (name only), or upload below.</small>
                <div class="admin-panel" style="margin-top:0.75rem;padding:1rem;">
                    <strong>Upload hero image</strong> (max 5 MB)
                    <div style="margin-top:0.5rem;">
                        <input type="file" id="cms-upload-dance-hero-file" accept="image/jpeg,image/png,image/gif,image/webp">
                        <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" id="cms-upload-dance-hero-btn" style="margin-left:0.5rem;">Upload &amp; fill filename</button>
                    </div>
                </div>
            </div>

            <div class="admin-field">
                <label for="hero_subtitle">Hero subtitle</label>
                <textarea id="hero_subtitle" class="cms-wysiwyg admin-input admin-textarea" name="hero_subtitle" required rows="4"><?= $h($viewModel->heroSubtitle) ?></textarea>
            </div>

            <div class="admin-field">
                <label for="hero_cta_label">Hero button label</label>
                <input type="text" id="hero_cta_label" name="hero_cta_label" required maxlength="120"
                       value="<?= $h($viewModel->heroCtaLabel) ?>" class="admin-input">
            </div>

            <fieldset class="admin-fieldset">
                <legend>About Dance — paragraphs</legend>
                <div class="admin-field">
                    <label for="about_p1">Paragraph 1 (rich text)</label>
                    <textarea id="about_p1" class="cms-wysiwyg admin-input admin-textarea" name="about_p1" rows="5"><?= $h($viewModel->aboutP1) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="about_p2">Paragraph 2</label>
                    <textarea id="about_p2" class="cms-wysiwyg admin-input admin-textarea" name="about_p2" rows="4"><?= $h($viewModel->aboutP2) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="about_p3">Paragraph 3 (rich text)</label>
                    <textarea id="about_p3" class="cms-wysiwyg admin-input admin-textarea" name="about_p3" rows="5"><?= $h($viewModel->aboutP3) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset">
                <legend>Card images (one filename per line)</legend>
                <p class="admin-hint">Filenames only, as in <code>/public/images/dance/</code>. Leave a block empty to keep config defaults on save.</p>
                <div class="admin-field">
                    <label for="featured_images_lines">Featured row (order matches featured events)</label>
                    <textarea id="featured_images_lines" class="admin-input admin-textarea" name="featured_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->featuredImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="friday_images_lines">Friday tab</label>
                    <textarea id="friday_images_lines" class="admin-input admin-textarea" name="friday_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->fridayImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="saturday_images_lines">Saturday tab</label>
                    <textarea id="saturday_images_lines" class="admin-input admin-textarea" name="saturday_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->saturdayImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="sunday_images_lines">Sunday tab</label>
                    <textarea id="sunday_images_lines" class="admin-input admin-textarea" name="sunday_images_lines" maxlength="8000" style="font-family:monospace;font-size:0.9rem;min-height:7rem;"><?= $h($viewModel->sundayImagesLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="featured_genre_labels_lines">Featured genre pills (one per line, order matches cards)</label>
                    <textarea id="featured_genre_labels_lines" class="admin-input admin-textarea" name="featured_genre_labels_lines" maxlength="500" style="font-family:monospace;font-size:0.9rem;min-height:4rem;"><?= $h($viewModel->featuredGenreLabelsLines) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset">
                <legend>Event detail page (<code>/dance/event/…</code>)</legend>
                <p class="admin-hint">Stored in <code>dance_settings</code>. Map pin uses venue name → coordinates JSON; unknown venues use default lat/lon.</p>
                <div class="admin-field">
                    <label for="breadcrumb_home_label">Breadcrumb: home label</label>
                    <input type="text" id="breadcrumb_home_label" name="breadcrumb_home_label" required maxlength="40"
                           value="<?= $h($viewModel->breadcrumbHomeLabel) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="breadcrumb_dance_label">Breadcrumb: dance label</label>
                    <input type="text" id="breadcrumb_dance_label" name="breadcrumb_dance_label" required maxlength="40"
                           value="<?= $h($viewModel->breadcrumbDanceLabel) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="event_detail_list_path">Breadcrumb: link back to programme</label>
                    <input type="text" id="event_detail_list_path" name="event_detail_list_path" required maxlength="120"
                           value="<?= $h($viewModel->eventDetailListPath) ?>" class="admin-input" placeholder="/dance">
                </div>
                <div class="admin-field">
                    <label for="event_detail_photos_context">Photos CMS context key</label>
                    <input type="text" id="event_detail_photos_context" name="event_detail_photos_context" required maxlength="80" pattern="[A-Za-z0-9_]+"
                           value="<?= $h($viewModel->eventDetailPhotosContext) ?>" class="admin-input">
                    <small class="admin-hint">Must match <code>photos.context</code> for dance event detail slots.</small>
                </div>
                <div class="admin-field">
                    <label for="event_detail_hero_fallback">Hero image fallback (relative to <code>/images/dance/</code>)</label>
                    <input type="text" id="event_detail_hero_fallback" name="event_detail_hero_fallback" required maxlength="255"
                           value="<?= $h($viewModel->eventDetailHeroFallback) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="event_detail_gallery_lines">Gallery fallbacks (exactly three lines, filenames/paths)</label>
                    <textarea id="event_detail_gallery_lines" name="event_detail_gallery_lines" class="admin-input admin-textarea" maxlength="2000" style="font-family:monospace;font-size:0.9rem;min-height:5rem;"><?= $h($viewModel->eventDetailGalleryLines) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="default_event_day">Default event day if missing in DB</label>
                    <input type="text" id="default_event_day" name="default_event_day" required maxlength="20" pattern="[a-z]+"
                           value="<?= $h($viewModel->defaultEventDay) ?>" class="admin-input" placeholder="friday">
                </div>
                <div class="admin-field">
                    <label for="event_detail_venue_country">Location line: country</label>
                    <input type="text" id="event_detail_venue_country" name="event_detail_venue_country" required maxlength="80"
                           value="<?= $h($viewModel->eventDetailVenueCountry) ?>" class="admin-input">
                </div>
                <div class="admin-field" style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:10rem;">
                        <label for="default_map_lat">Default map latitude</label>
                        <input type="text" id="default_map_lat" name="default_map_lat" required maxlength="20"
                               value="<?= $h($viewModel->defaultMapLat) ?>" class="admin-input">
                    </div>
                    <div style="flex:1;min-width:10rem;">
                        <label for="default_map_lon">Default map longitude</label>
                        <input type="text" id="default_map_lon" name="default_map_lon" required maxlength="20"
                               value="<?= $h($viewModel->defaultMapLon) ?>" class="admin-input">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="venue_coordinates_json">Venue → map pin (JSON object)</label>
                    <textarea id="venue_coordinates_json" name="venue_coordinates_json" class="admin-input admin-textarea" maxlength="16000" style="font-family:monospace;font-size:0.85rem;min-height:14rem;"><?= $h($viewModel->venueCoordinatesJson) ?></textarea>
                </div>
            </fieldset>

            <fieldset class="admin-fieldset">
                <legend>Artist detail (<code>/dance/artist/…</code>)</legend>
                <p class="admin-hint">Breadcrumbs reuse the event-detail “home / dance / list path” fields above. Narrative + tracks still come from <code>dance.php</code> → <code>artist_music</code> per slug.</p>
                <div class="admin-field">
                    <label for="dance_images_base_path">Public URL prefix for dance images</label>
                    <input type="text" id="dance_images_base_path" name="dance_images_base_path" required maxlength="120"
                           value="<?= $h($viewModel->artistDetailDanceImagesBasePath) ?>" class="admin-input" placeholder="/images/dance/">
                </div>
                <div class="admin-field">
                    <label for="artist_detail_photos_context_hero">Photos context: artist hero (slot key = artist slug)</label>
                    <input type="text" id="artist_detail_photos_context_hero" name="artist_detail_photos_context_hero" required maxlength="80" pattern="[A-Za-z0-9_]+"
                           value="<?= $h($viewModel->artistDetailPhotosContextHero) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="artist_detail_photos_context_schedule">Photos context: schedule strip</label>
                    <input type="text" id="artist_detail_photos_context_schedule" name="artist_detail_photos_context_schedule" required maxlength="80" pattern="[A-Za-z0-9_]+"
                           value="<?= $h($viewModel->artistDetailPhotosContextSchedule) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="artist_detail_photos_context_music">Photos context: music block</label>
                    <input type="text" id="artist_detail_photos_context_music" name="artist_detail_photos_context_music" required maxlength="80" pattern="[A-Za-z0-9_]+"
                           value="<?= $h($viewModel->artistDetailPhotosContextMusic) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="artist_detail_hero_fallback">Hero fallback if CMS + DB image empty</label>
                    <input type="text" id="artist_detail_hero_fallback" name="artist_detail_hero_fallback" required maxlength="255"
                           value="<?= $h($viewModel->artistDetailHeroFallback) ?>" class="admin-input">
                </div>
                <div class="admin-field">
                    <label for="artist_detail_schedule_fallbacks_json">Schedule image fallbacks (slug → path; include <code>"default"</code>)</label>
                    <textarea id="artist_detail_schedule_fallbacks_json" name="artist_detail_schedule_fallbacks_json" class="admin-input admin-textarea" maxlength="8000" style="font-family:monospace;font-size:0.85rem;min-height:8rem;"><?= $h($viewModel->artistDetailScheduleFallbacksJson) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="artist_detail_music_profile_slots_json">Music block: profile row slot keys (slug → key; include <code>"default"</code>)</label>
                    <textarea id="artist_detail_music_profile_slots_json" name="artist_detail_music_profile_slots_json" class="admin-input admin-textarea" maxlength="4000" style="font-family:monospace;font-size:0.85rem;min-height:5rem;"><?= $h($viewModel->artistDetailMusicProfileSlotsJson) ?></textarea>
                </div>
                <div class="admin-field">
                    <label for="artist_detail_music_album_slots_json">Music block: album cover slot keys</label>
                    <textarea id="artist_detail_music_album_slots_json" name="artist_detail_music_album_slots_json" class="admin-input admin-textarea" maxlength="4000" style="font-family:monospace;font-size:0.85rem;min-height:5rem;"><?= $h($viewModel->artistDetailMusicAlbumSlotsJson) ?></textarea>
                </div>
                <div class="admin-field" style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:12rem;">
                        <label for="artist_detail_music_profile_fallback">Profile image file fallback</label>
                        <input type="text" id="artist_detail_music_profile_fallback" name="artist_detail_music_profile_fallback" required maxlength="255"
                               value="<?= $h($viewModel->artistDetailMusicProfileFallback) ?>" class="admin-input">
                    </div>
                    <div style="flex:1;min-width:12rem;">
                        <label for="artist_detail_music_album_fallback">Album cover file fallback</label>
                        <input type="text" id="artist_detail_music_album_fallback" name="artist_detail_music_album_fallback" required maxlength="255"
                               value="<?= $h($viewModel->artistDetailMusicAlbumFallback) ?>" class="admin-input">
                    </div>
                </div>
                <div class="admin-field" style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:10rem;">
                        <label for="artist_detail_default_location">Default “location” if not in artist_music</label>
                        <input type="text" id="artist_detail_default_location" name="artist_detail_default_location" required maxlength="80"
                               value="<?= $h($viewModel->artistDetailDefaultLocation) ?>" class="admin-input">
                    </div>
                    <div style="flex:1;min-width:10rem;">
                        <label for="artist_detail_default_album_title">Default album title</label>
                        <input type="text" id="artist_detail_default_album_title" name="artist_detail_default_album_title" required maxlength="120"
                               value="<?= $h($viewModel->artistDetailDefaultAlbumTitle) ?>" class="admin-input">
                    </div>
                    <div style="flex:1;min-width:10rem;">
                        <label for="artist_detail_default_album_sub">Default album subtitle</label>
                        <input type="text" id="artist_detail_default_album_sub" name="artist_detail_default_album_sub" required maxlength="120"
                               value="<?= $h($viewModel->artistDetailDefaultAlbumSub) ?>" class="admin-input">
                    </div>
                </div>
                <div class="admin-field" style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="flex:1;min-width:8rem;">
                        <label for="artist_detail_gallery_target_count">Gallery min count (top with config)</label>
                        <input type="text" id="artist_detail_gallery_target_count" name="artist_detail_gallery_target_count" required maxlength="3" pattern="[0-9]+"
                               value="<?= $h($viewModel->artistDetailGalleryTargetCount) ?>" class="admin-input">
                    </div>
                    <div style="flex:1;min-width:8rem;">
                        <label for="artist_detail_hero_tagline_max_chars">Hero tagline max (bio teaser)</label>
                        <input type="text" id="artist_detail_hero_tagline_max_chars" name="artist_detail_hero_tagline_max_chars" required maxlength="3" pattern="[0-9]+"
                               value="<?= $h($viewModel->artistDetailHeroTaglineMaxChars) ?>" class="admin-input">
                    </div>
                </div>
                <div class="admin-field">
                    <label for="artist_detail_gallery_stats_json">Stats row fallback (JSON array of <code>{"num","label"}</code>)</label>
                    <textarea id="artist_detail_gallery_stats_json" name="artist_detail_gallery_stats_json" class="admin-input admin-textarea" maxlength="4000" style="font-family:monospace;font-size:0.85rem;min-height:6rem;"><?= $h($viewModel->artistDetailGalleryStatsJson) ?></textarea>
                </div>
            </fieldset>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Save Dance page</button>
                <a href="/admin/dance" class="admin-btn admin-btn-secondary">Dance CMS hub</a>
            </div>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/js/admin-cms-editors.js?v=1"></script>
<?php require __DIR__ . '/../../partials/footer.php'; ?>
</body>
</html>
