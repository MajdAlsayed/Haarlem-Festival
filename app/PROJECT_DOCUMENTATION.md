# Haarlem Festival — Project documentation

This document explains the project for development and for the **project assessment** (how to run it, how the code is structured, and what to say when explaining it).

---

## 1. Project overview

**Haarlem Festival** is a multi-page website for a fictional festival in Haarlem. It includes:

- A **homepage** with hero, upcoming events (one per category), about, and map sections.
- A **Dance** page with hero, featured events, all events by day (Friday/Saturday/Sunday), and artists.
- Shared **header** (logo, nav, search, language, cart) and **footer** (logo, links, social icons, app store buttons).

The app is **plain PHP** with **no framework**: no Laravel, Symfony, or Slim. We use **Composer** for autoloading (PSR-4) and **Phinx** for database migrations and seeds. Routing is a simple `switch` in `public/index.php`.

---

## 2. Tech stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.x |
| Framework | **None** (vanilla PHP) |
| Autoloading | Composer, PSR-4 `App\` → `src/` |
| Database | MySQL (via PDO) |
| Migrations & seeds | Phinx |
| Server | Docker: nginx, PHP-FPM, MySQL |

---

## 3. Folder structure

```
app/
├── public/                 # Web root
│   └── index.php           # Entry point; parses URI and calls controller
├── src/
│   ├── Config/             # Fallback config (used only if DB tables empty)
│   ├── Contracts/          # Interfaces (EventRepositoryInterface, PageRepositoryInterface)
│   ├── Controllers/        # HomeController, DanceController
│   ├── Core/               # Database connection
│   ├── Models/             # Event, Page, etc.
│   ├── Repositories/       # Data access (Event, EventType, Page, Settings, Menu, DanceSettings, Artists)
│   ├── Services/           # EventService, PageService (use repository interfaces)
│   ├── ViewModels/         # HomeViewModel, DanceViewModel
│   └── Views/              # PHP templates (Home/, Dance/, partials/)
├── db/
│   ├── migrations/         # Phinx migrations
│   └── seeds/              # Seeders (pages, events, settings, menu, dance, artists)
├── HARDCODE_AUDIT.md       # What is in DB vs still hardcoded
└── PROJECT_DOCUMENTATION.md  # This file
```

---

## 4. Request flow

1. **Entry:** Browser hits `public/index.php`. A `switch` on the path calls the right controller.
2. **Routes:** `'/'` and `'/home'` → `HomeController::index()`; `'/dance'` → `DanceController::index()`.
3. **Controller:** Uses **Services** (which use **Repositories**), builds a **ViewModel**, then `require`s the view.
4. **View:** Templates use partials (header, footer). Views get data from the ViewModel and from **Repositories** for global data (settings, menu).

---

## 5. Database-driven configuration

Site name, nav, event card images, dance content, and similar values are stored in the **database**, not hardcoded in code.

| What                                           | Table                | Where used            |
|------------------------------------------------|----------------------|-----------------------|
| Site name, logo, footer, defaults, CSS version | `site_settings`      | Header, footer, views |
| Nav links (path + label)                       | `menu_items`         | Header                |
| Card image + INFO link per event type          | `event_types`        | Homepage event cards  |
| Dance hero, day images, genres, featured card  | `dance_settings`     | Dance page            |
| Dance artists                                  | `artists`            | Dance page            |
| Category order for homepage                    | `event_types`        | HomeController        |
| Venue city                                     | `venues.city`        | Event cards           |

Config files in `Config/` are used only as **fallback** when the corresponding DB table is empty.

---

## 6. Key files

| File                           | Role                                                              |
|--------------------------------|-------------------------------------------------------------------|
| `public/index.php`             | Entry point, route switch, exception handler.                     |
| `HomeController`               | Home page + one event per category; uses EventTypeRepository.     |
| `DanceController`              | Dance events by day; DanceViewModel.                              |
| `EventRepository`              | Events + venue city, card_image, info_path. Implements interface. |
| `PageRepository`               | Page by slug. Implements interface.                               |
| `EventService` / `PageService` | Use repo interfaces + Validator.                                  |
| `SettingsRepository`           | site_settings for header, footer, views.                          |
| `MenuRepository`               | Nav links from menu_items.                                        |
| `header.php` / `footer.php`    | MenuRepository + SettingsRepository.                              |

---

## 7. How to run

```bash
docker compose up -d
docker compose run --rm php composer install
docker compose run --rm php vendor/bin/phinx migrate
docker compose run --rm php vendor/bin/phinx seed:run
```

Then open the site (e.g. http://localhost:8080).

---

## 8. Explaining the code at assessment

- **No framework:** “The project is plain PHP. We use Composer for autoloading and Phinx for migrations. Routing is a simple switch in index.php.”
- **Structure:** “Controllers use services, services use repositories (via interfaces). Views get ViewModels and sometimes call repositories for global data like settings and menu.”
- **Config in DB:** “Site name, nav links, event card images, dance content, and artists are in the database so they can be changed without editing code. Config files are only fallback when tables are empty.”
- **Interfaces:** “EventRepository and PageRepository implement interfaces; services depend on those interfaces so we can swap implementations.”

This document and `HARDCODE_AUDIT.md` give a full picture of the project and what is (or isn’t) hardcoded.
