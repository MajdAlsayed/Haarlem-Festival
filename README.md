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

### 4. Seed the database (pages + events)

```bash
docker compose run --rm php vendor/bin/phinx seed:run
```

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
```

**Pages:** Each person can add their own page seeder (example `DancePageSeeder`). Use `INSERT IGNORE` so seed order doesn’t matter. In app code and when inserting into `page_blocks`, always get `page_id` by slug — never hardcode IDs.

---

## Test the site & admin export (local)

1. **Start Docker** (from the repo root):
   ```bash
   docker compose up -d
   ```

2. **Open the site:** [http://localhost](http://localhost) (nginx on port **80**).

3. **Database UI (phpMyAdmin):** [http://localhost:8080](http://localhost:8080)  
   - Server: `mysql` (if asked from host, use `127.0.0.1` port **3307**, user `developer`, password `secret123`, database `HaarlemFestivaldb`).

4. **Migrations & seeds** (if not done yet):
   ```bash
   docker compose run --rm php vendor/bin/phinx migrate
   docker compose run --rm php vendor/bin/phinx seed:run
   ```

5. **Demo admin + sample orders** (for **Export orders** CMS):
   ```bash
   docker compose run --rm php vendor/bin/phinx seed:run -s AdminOrdersSampleSeeder
   ```
   - **Login:** `admin@haarlem.test`  
   - **Password:** `Admin123!`

6. **Export orders page (admin only):** after login, open  
   **[http://localhost/admin/orders/export](http://localhost/admin/orders/export)**  
   Choose columns (include **Total amount** and **Paid at**), pick CSV or Excel, download.

If you already have rows in `orders`, the sample seeder skips inserting duplicate demo orders but still ensures the demo admin user exists.

---

## Security overview

See **`app/docs/SECURITY.md`** for SQL injection / XSS / CSRF / CAPTCHA / ticket tokens and HTTP headers.

---

## Admin CMS (administrator only)

Log in with an **admin** user (see **Demo admin + sample orders** above if you need the seeded account).  
CMS needs **`composer install`** so **`ezyang/htmlpurifier`** is present (HTML cleanup for TinyMCE fields).

| URL | What |
|-----|------|
| `/admin/orders` | **View orders** — table of all orders (read-only) |
| `/admin/orders/export` | **Export orders** — CSV / Excel, selectable columns |
| `/admin/cms/homepage` | **Edit homepage** — title (`pages` slug `home`) + hero / welcome / about (`site_settings` `cms_home_*`) |
| `/admin/cms/dance` | **Edit Dance page** — copy, headings, hero, image lists (`dance_settings` + merge with `config/dance.php`) |

Each page includes links to the other admin CMS screens.
