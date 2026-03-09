# Controllers, Repositories & Database – Data Audit

## Summary

Audit of data flow from database → repositories → controllers → views. Two robustness fixes were applied; everything else is consistent.

---

## 1. Controllers

| Controller | Data source | Uses |
|------------|-------------|------|
| **HomeController** | PageRepository (pages), EventService (events), EventTypeRepository (type order) | Page by slug `home`; events via EventService::getAll() then one per category; type order for display. |
| **DanceController** | EventService | getByCategory('dance'), getByCategoryAndDay('dance', day). Returns Event[] from EventRepository. |
| **JazzController** | JazzRepository, JazzSettingsRepository, Config/jazz.php | index: getAll() events + config (event_card_images, all_events_order). Artist pages: getByTitle(title) + artist_pages (DB or config). |

**Findings:** All controllers use the correct repositories and pass the right data to views.

---

## 2. Repositories & database columns

### EventRepository (events + event_types + venues)

- **Tables:** `events`, `event_types`, `venues`
- **Columns used:** event_id, event_type_id, venue_id, title, description, event_day, start_time, event_type_name, venue_name, venue_city, card_image, info_path.
- **Not selected:** end_time, hall, seats, price (used only for Jazz).
- **Used by:** HomeController, DanceController. Event model has no end_time/hall/price/seats; that’s correct for Home/Dance.

### JazzRepository (jazz events only)

- **Tables:** `events`, `event_types`, `venues`
- **Columns:** event_id, title, description, event_day, start_time, end_time, hall, seats, price, venue_name, venue_city.
- **Fallback:** If DB doesn’t have end_time, hall, seats, price (e.g. migrations not run), a base query without those columns is used so the Jazz page still works.
- **Used by:** JazzController only. Returns array of associative arrays (not Event model).

### JazzSettingsRepository (jazz_settings)

- **Table:** `jazz_settings` (setting_key, setting_value).
- **Fallback:** If the table is missing or the query fails, config from `Config/jazz.php` is returned.
- **Used by:** JazzController for artist_pages (and any other jazz settings).

### DanceSettingsRepository (dance_settings)

- **Table:** `dance_settings`
- **Fallback:** If the table is missing or the query fails, config from `Config/dance.php` is returned.
- **Used by:** Dance view (loaded in template).

### SettingsRepository (site_settings)

- **Table:** `site_settings`
- **Used by:** All views for site-wide settings (css_version, default_event_time, logo_src, etc.). Falls back to Config/app.php when table is empty.

### PageRepository (pages)

- **Table:** `pages` (page_id, slug, title, is_published, published_at).
- **Used by:** HomeController for slug `home`.

### EventTypeRepository (event_types)

- **Used by:** HomeController for category order (getAllIdsOrdered()).

### ArtistsRepository (artists)

- **Table:** `artists` (name, bio, image_filename, sort_order, slug from migration).
- **Returns:** name, bio, image (mapped from image_filename).
- **Used by:** Dance view for the artists section.

---

## 3. Database schema (events)

From migrations:

1. **create_events_table:** event_id, event_type_id, venue_id, title, description
2. **add_event_day_and_start_time:** event_day, start_time
3. **add_jazz_fields_to_events:** hall (after description), end_time (after start_time), price (after end_time); unique index on (event_type_id, title, event_day, start_time)
4. **add_seats_to_events:** seats (after hall)

Column order in `events`: event_id, event_type_id, venue_id, title, description, hall, seats, event_day, start_time, end_time, price.

JazzRepository selects these and maps them; Jazz views use event_id, title, description, event_day, start_time, end_time, hall, seats, price, venue_name, venue_city. All come from the DB or the base-query fallback.

---

## 4. Fixes applied

1. **JazzSettingsRepository:** Wrapped the `jazz_settings` query in try/catch. On failure (e.g. table missing), returns `Config/jazz.php` so Jazz still works without that migration.
2. **DanceSettingsRepository:** Same for `dance_settings` and `Config/dance.php`.
3. **ArtistsRepository:** Docblock corrected: return shape uses `image`, not `image_filename`.

---

## 5. Recommendations

- **Run migrations** so all columns exist: `php vendor/bin/phinx migrate` (or via Docker: `docker compose exec php php /app/vendor/bin/phinx migrate`).
- **Seed data:** JazzSeeder (and JazzSettingsSeeder if you use DB for jazz settings), DanceSettingsSeeder if you use dance_settings, and any seeders for pages/site_settings so Home and Dance have data.
- **Home 404:** If `pages` has no row with slug `home` and is_published = 1, HomeController throws NotFoundException. Ensure a seed or manual insert for the home page.

---

## 6. Data flow checklist

| Page / feature | Controller | Repository / service | DB tables | Status |
|----------------|------------|----------------------|-----------|--------|
| Home | HomeController | PageRepository, EventService, EventTypeRepository | pages, events, event_types, venues | OK |
| Dance | DanceController | EventService, DanceSettingsRepository, ArtistsRepository (in view) | events, event_types, venues, dance_settings, artists | OK |
| Jazz home | JazzController | JazzRepository, Config | events, event_types, venues | OK |
| Jazz artist (Gumbo/Karsu/Gare du Nord) | JazzController | JazzRepository, JazzSettingsRepository / Config | events, jazz_settings (or config) | OK |

All data coming from the database is aligned with the repository methods and controller usage above.
