# Sprint checklist (teacher requirements)

Back end and front end both need to be done before end of sprint.

## Done
- [x] **Repo mapping in private method** – `EventRepository::mapRowToEvent()`, `PageRepository::mapRowToPage()` are private.
- [x] **Interfaces** – `EventRepositoryInterface`, `PageRepositoryInterface` in `src/Contracts/`; repos implement them.
- [x] **Services** – `EventService`, `PageService`; controllers use services only (no repos in controllers).
- [x] **Prepared statements** – Queries use PDO prepared statements (no raw user input in SQL).
- [x] **Exceptions / error handling** – `AppException`, `NotFoundException`, `ValidationException`; `set_exception_handler` in `index.php`; `Views/error.php`.
- [x] **View models** – `HomeViewModel`, `DanceViewModel`; controllers pass view model; views use `$viewModel->...`.
- [x] **Validation layer** – `Validator::validateSlug()`, `validateEventCategory()` in `src/Validation/`; used in PageService and EventService. user input (forms, query params) before use; keep PDO as the “blocker” for SQL.

## To do

- [ ] **Back end** – Any remaining controllers/routes for food, history, stories, tickets, etc.
- [ ] **Front end** – All sprint pages (home ✓, dance ✓, food, history, stories, etc.) with views/CSS/JS.

## Notes
- Controllers should depend on **interfaces** (e.g. `EventRepositoryInterface`) so you can swap implementations or mock in tests.
- When you add a service, inject the repo (interface) into the service; inject the service into the controller.
