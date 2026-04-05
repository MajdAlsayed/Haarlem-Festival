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

This run includes **`AdminUserSeeder`** (admin login below), **`JazzSeeder`**, **`JazzSettingsSeeder`**, discography/audio seeders, and the rest. Use it after migrations so you can sign in and open **`/admin/jazz`** without extra steps. For the **employee** scanner account, run **`EmployeeUserSeeder`** separately (see §5b) after migrations.

For a first-time setup, run all seeders (no `-s`) so dependencies run in the right order. To run a specific seeder:

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

**`AdminOrdersSampleSeeder`** ensures **`admin@haarlem.test`** and inserts **demo orders** (paid/pending) for **`/admin/orders/export`** when the `orders` table is empty. If orders already exist, it skips inserting demo orders but still ensures the demo admin user.

### 5. Admin login (pages CMS + Jazz CMS + tickets CMS)

Sign in at **`/login`** with:

- **Email:** `admin@haarlem.test`
- **Password:** `Admin123!`

Then open **`/admin`** for pages, **`/admin/jazz`** for the jazz CMS, or **`/admin/tickets`** for ticket copy (same account).

If the admin user is missing (e.g. you never ran full seeds), run:

```bash
docker compose run --rm php vendor/bin/phinx seed:run -s AdminUserSeeder
```

Re-run `AdminUserSeeder` anytime to reset that password. Change these in production.

### 5b. Employee login (ticket scanner only — assessment demo)

After **`vendor/bin/phinx migrate`** (adds the `employee` role), seed the door-staff account:

```bash
docker compose run --rm php vendor/bin/phinx seed:run -s EmployeeUserSeeder
```

Sign in at **`/login`** with:

- **Email:** `employee@haarlem.test`
- **Password:** `Employee123!`

Then open **`/admin/scan`** (or use the **🎫** link in the header). This account **cannot** use the full CMS (`/admin`, orders export, etc.) — only scan tickets.

### 6. Jazz CMS (`/admin/jazz`)

After logging in as admin, open **`/admin/jazz`** (or use the **Jazz** card on the dashboard). From there you can:

- **Events** — create, edit, or delete jazz rows in `events` (venue, day, times, hall, seats, price, long description). Optional **preview audio** path for the Gumbo Kings–style player (`event_audio`).
- **Layout & images** — homepage hero filename, artist page titles/taglines/heroes, **event card images** (`Title|filename` per line), and **day / “All events” ordering** (one title per line; must match event titles in the database).
- **Discography** — manage `artist_discography` tracks per **artist slug** (e.g. `karsu`); image and audio paths are under `public/images/jazz/` and `public/audio/` as today.

Public jazz pages read settings from **`jazz_settings`** merged with defaults in `app/src/Config/jazz.php`. If a key is not in the database, the file default is used.

**Pages:** Each person can add their own page seeder (example `DancePageSeeder`). Use `INSERT IGNORE` so seed order does not matter. In app code and when inserting into `page_blocks`, always get `page_id` by slug — never hardcode IDs.

### Visitor: invoice, tickets, almost sold out

- After **checkout**, open **`/account/orders`** (header **My orders**) → pick an order for a **printable invoice** and **ticket codes**. Same data is appended to **`app/storage/mail/orders.log`** inside the PHP container (and `mail()` is attempted if your PHP host can send).
- **`/tickets`** shows **Almost sold out** when ≥ **90%** of capacity is used (sold + items in active carts), and **Sold out** hides the buy button. Capacity uses **`events.seats`** or **`sessions.tickets_available`**; passes are uncapped. Run **`vendor/bin/phinx migrate`** so **`backfill_event_seats`** sets **`events.seats`** where it was still NULL (older seeds left it empty). Migrations **`demo_low_seats_for_tickets_page`**, **`gumbo_kings_demo_seats`**, and **`dance_demo_seats`** cap a few rows (e.g. **Gumbo Kings · Thursday** on jazz, **Nicky Romero & Afrojack · Friday** plus the first dance `ticket_details` row) so **“Only X left”** / **sold out** show without selling hundreds of tickets. **Passes** at the top of `/tickets` stay uncapped.

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
   docker compose run --rm php vendor/bin/phinx seed:run -s EmployeeUserSeeder
   ```
5. **Assessment smoke test (10 min demo):**
   1. **Visitor:** homepage → **`/tickets`** (try **jazz / dance / history / stories** tabs, note caps if seats are low) → add paid items to cart → **register** (if needed) → **checkout** (demo or Stripe) → success page → **My orders** → open order (**invoice + tickets**). Optional: `docker compose exec php cat /app/storage/mail/orders.log` (tail of log).
   2. **Admin:** **`/admin/cms/homepage`** (WYSIWYG) → **`/admin/jazz`** or tickets CMS to change **seats** → **`/admin/orders`** + **Codes** link + **export**.
   3. **Employee:** logout → **`employee@haarlem.test`** / **`Employee123!`** → **`/admin/scan`** → scan a code from **My orders** (as visitor) or **admin order Codes**.

6. **Demo admin + sample orders** (for **Export orders** CMS), if needed:
   ```bash
   docker compose run --rm php vendor/bin/phinx seed:run -s AdminOrdersSampleSeeder
   ```

7. **Export orders page (admin only):** after login, open  
   **[http://localhost/admin/orders/export](http://localhost/admin/orders/export)**  
   Choose columns (include **Total amount** and **Paid at**), pick CSV or Excel, download.

### Stripe checkout (optional — card + iDEAL in test mode)

1. Run **`composer install`** and **`vendor/bin/phinx migrate`** (adds `orders.stripe_checkout_session_id`).
2. In the [Stripe Dashboard](https://dashboard.stripe.com/test/apikeys), copy a **secret test key** (`sk_test_...`).
3. Set environment variables for the PHP container (e.g. in `docker-compose.yml` under `php` → `environment`, or your host):

   - **`STRIPE_SECRET_KEY`** — your `sk_test_...` key  
   - **`APP_PUBLIC_URL`** — `http://localhost` (must match how you open the site so Stripe can redirect back)

4. Restart containers, add tickets to the cart, log in, open **`/checkout`**, and use **Pay with card or iDEAL**.  
   The **Confirm without payment (demo)** button stays available for local demos without keys.

---

## Security overview

See **`app/docs/SECURITY.md`** for SQL injection / XSS / CSRF / CAPTCHA / ticket tokens and HTTP headers.

---

## Admin CMS (administrator only)

Log in with an **admin** user (see **Admin login** above if you need the seeded account).  
CMS needs **`composer install`** so **`ezyang/htmlpurifier`** is present (HTML cleanup for TinyMCE fields).

| URL                    | What                                                                                                       |
|------------------------|------------------------------------------------------------------------------------------------------------|
| `/admin/orders`        | **View orders** — table of all orders (read-only)                                                          |
| `/admin/orders/export` | **Export orders** — CSV / Excel, selectable columns                                                        |
| `/admin/cms/homepage`  | **Edit homepage** — title (`pages` slug `home`) + hero / welcome / about (`site_settings` `cms_home_*`)    |
| `/admin/cms/dance`     | **Edit Dance page** — copy, headings, hero, image lists (`dance_settings` + merge with `config/dance.php`) |

Each page includes links to the other admin CMS screens.
