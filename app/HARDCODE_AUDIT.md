# Hardcode audit

## Stored in the database (no longer in config files)

| What                                                                                              | Table / source                                                                          |
|---------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------|
| Site name, logo, home path, icons path, default location/time, CSS version, footer icons & labels | `site_settings` (SettingsRepository)                                                    |
| Nav links (path + label)                                                                          | `menu_items` (MenuRepository)                                                           |
| Homepage event card image + INFO path per event type                                              | `event_types.card_image`, `event_types.info_path` (EventRepository fetches with events) |
| Dance: hero image, day images, genres, featured first card                                        | `dance_settings` (DanceSettingsRepository)                                              |
| Dance page artists                                                                                | `artists` (ArtistsRepository)                                                           |
| Homepage category order                                                                           | `event_types` (EventTypeRepository::getAllIdsOrdered())                                 |
| Venue city on event cards                                                                         | `venues.city` (Event.venueCity)                                                         |

Config files (`Config/app.php`, `Config/nav.php`, `Config/events.php`, `Config/dance.php`) are kept only as **fallback** when the corresponding DB tables are empty (e.g. before running seeders).

---

## Remaining hardcoded (by design or acceptable)

- **Routing** (public/index.php): routes `'/'`, `'/home'`, `'/dance'`.
- **Page slug** (HomeController): `getBySlug('home')`.
- **Database credentials** (Core/Database.php): should move to environment variables for production.
- **Body copy** in views (hero, welcome, about, etc.): content text; can later come from CMS.
- **Dance day keys** (friday/saturday/sunday): match DB `event_day` values.
- **Asset paths** (`/images/`, `/css/`): standard web paths.
