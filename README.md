# Haarlem Festival

## Running the project

### 1. Start the stack

```bash
docker compose up -d
```

(Use `docker compose up` without `-d` to see logs in the terminal.)

### 2. Install PHP dependencies (if needed)

Dependencies are installed automatically when the PHP container starts. To install or update manually:

```bash
docker compose run --rm php composer install
```

### 3. Run database migrations

```bash
docker compose run --rm php vendor/bin/phinx migrate
```

### 4. Seed the database (pages, events, jazz, admin user)

```bash
docker compose run --rm php vendor/bin/phinx seed:run
```

This run includes **`AdminUserSeeder`** (admin login below), **`JazzSeeder`**, **`JazzSettingsSeeder`**, discography/audio seeders, and the rest. Use it after migrations so you can sign in and open **`/admin/jazz`** without extra steps.

For a first-time setup, run all seeders (no `-s`) so dependencies run in the right order. To re-seed only one part:

To run a specific seeder:

```bash
docker compose run --rm php vendor/bin/phinx seed:run -s PageSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s EventSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s ArtistsSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s ArtistPhotosSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s PhotosSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s DancePageSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s StoriesSeeder
docker compose run --rm php vendor/bin/phinx seed:run -s AdminOrdersSampleSeeder
```

(`AdminOrdersSampleSeeder` adds one sample paid order only when the `orders` table is empty; run after `EventSeeder`.)

### 5. Admin login (pages CMS + Jazz CMS)

Sign in at **`/login`** with:

- **Email:** `admin@haarlem.test`
- **Password:** `Admin123!`

Then open **`/admin`** for pages, or **`/admin/jazz`** for the jazz CMS (same account).

If the admin user is missing (e.g. you never ran full seeds), run:

```bash
docker compose run --rm php vendor/bin/phinx seed:run -s AdminUserSeeder
```

Re-run `AdminUserSeeder` anytime to reset that password. Change these in production.

### 6. Jazz CMS (`/admin/jazz`)

After logging in as admin, open **`/admin/jazz`** (or use the **Jazz** card on the dashboard). From there you can:

- **Events** — create, edit, or delete jazz rows in `events` (venue, day, times, hall, seats, price, long description). Optional **preview audio** path for the Gumbo Kings–style player (`event_audio`).
- **Layout & images** — homepage hero filename, artist page titles/taglines/heroes, **event card images** (`Title|filename` per line), and **day / “All events” ordering** (one title per line; must match event titles in the database).
- **Discography** — manage `artist_discography` tracks per **artist slug** (e.g. `karsu`); image and audio paths are under `public/images/jazz/` and `public/audio/` as today.

Public jazz pages read settings from **`jazz_settings`** merged with defaults in `app/src/Config/jazz.php`. If a key is not in the database, the file default is used.

**Pages:** Each person can add their own page seeder (example `DancePageSeeder`). Use `INSERT IGNORE` so seed order doesn’t matter. In app code and when inserting into `page_blocks`, always get `page_id` by slug — never hardcode IDs.   
