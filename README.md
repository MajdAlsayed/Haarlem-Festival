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
