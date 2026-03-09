# Photos loaded from the database – checklist

## From database

| Where | Source | Table / context |
|-------|--------|------------------|
| **Dance index** – hero, featured cards, Fri/Sat/Sun images | `DanceSettingsRepository` | `dance_settings` |
| **Dance index** – artist list images | `ArtistsRepository` | `artists.image_filename` |
| **Artist detail** – gallery (4 images) + career image | `ArtistsRepository::getPhotoFilenamesByArtistId()` | `artist_photos` |
| **Artist detail** – schedule section image | `PhotosRepository::getFilename('dance_artist_schedule')` | `site_photos` |
| **Artist detail** – music section (profile, album, track covers) | `PhotosRepository::getFilename('dance_artist_music', key)` | `site_photos` |
| **Event detail** – hero + 3 gallery images | `PhotosRepository::getFilename('dance_event_detail', key)` | `site_photos` |
| **Artist detail** – hero | `PhotosRepository::getFilename('dance_artist_hero', slug)` or `artists.image_filename` | `site_photos` (key = artist slug) or `artists` |

## Still hardcoded (not from DB)

| Where | Current value | Note |
| **About page** | `About-haarlem.jpg` | Static; could add to `site_settings` or `site_photos` later |
| **Homepage event cards** | `$category->cardImage` or config fallback | From event type / config; not in `artist_photos` / `site_photos` |
| **Icons** (date, location, music, etc.) | `/images/icons/...` | UI icons; usually stay in code/config |

## Tables used

- **artist_photos** – per-artist gallery (and first image used as career image).
- **site_photos** – event detail, artist schedule, artist music, artist hero overrides (`dance_*` contexts).
- **dance_settings** – dance page hero and section images (JSON).
- **artists** – main artist image (`image_filename`).
