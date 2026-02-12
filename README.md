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
```

