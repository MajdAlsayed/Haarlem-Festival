# Dance Review Order


## 1) Main Controllers

1. [EventDetailController](app/src/Controllers/EventDetailController.php)
2. [ArtistController](app/src/Controllers/ArtistController.php)
3. [AdminDanceController](app/src/Controllers/AdminDanceController.php)
4. [DanceController](app/src/Controllers/DanceController.php)

## 2) Services

5. [DanceService](app/src/Services/DanceService.php)
6. [ArtistService](app/src/Services/ArtistService.php)
7. [EventService](app/src/Services/EventService.php)
8. [TicketAvailabilityService](app/src/Services/TicketAvailabilityService.php)

## 3) Repositories

9. [DanceSettingsRepository](app/src/Repositories/DanceSettingsRepository.php)
10. [DanceCmsRepository](app/src/Repositories/DanceCmsRepository.php)
11. [ArtistsRepository](app/src/Repositories/ArtistsRepository.php)
12. [EventRepository](app/src/Repositories/EventRepository.php)
13. [PhotosRepository](app/src/Repositories/PhotosRepository.php)
14. [TicketDetailsRepository](app/src/Repositories/TicketDetailsRepository.php)
15. [TicketsRepository](app/src/Repositories/TicketsRepository.php)
16. [TicketRepository](app/src/Repositories/TicketRepository.php)
17. [CartRepository](app/src/Repositories/CartRepository.php)

## 4) Admin Form + Mapping

18. [AdminDanceEditViewModel](app/src/ViewModels/AdminDanceEditViewModel.php)
19. [DanceEdit View](app/src/Views/Admin/DanceEdit.php)

## 5) Runtime View Models

20. [EventDetailViewModel](app/src/ViewModels/EventDetailViewModel.php)
21. [ArtistDetailViewModel](app/src/ViewModels/ArtistDetailViewModel.php)
22. [DanceViewModel](app/src/ViewModels/DanceViewModel.php)

## 6) Hardcode -> DB Story

23. [Dance Config](app/src/Config/dance.php)
24. [Migration: Event Detail Settings Seed](app/db/migrations/20260413120000_seed_dance_event_detail_settings.php)
25. [Migration: Artist Detail Settings Seed](app/db/migrations/20260413140000_seed_dance_artist_detail_settings.php)
26. [DanceSettingsSeeder](app/db/seeds/DanceSettingsSeeder.php)
