<?php

namespace App\Contracts\ServiceInterface;

use App\ViewModels\AdminDanceEditViewModel;

interface AdminDanceServiceInterface
{
    public function appSettings(): array;

    /** @return array[] */
    public function listEventsForAdmin(): array;

    /** @return array[] */
    public function listVenues(): array;

    public function getEventForEdit(int $eventId): ?array;

    public function getEventAudio(int $eventId): ?array;

    public function saveEvent(array $post): int;

    public function deleteEvent(int $eventId): void;

    /** @return array[] */
    public function listArtists(): array;

    public function getArtistForEdit(string $slug): ?array;

    public function saveArtist(array $post): void;

    public function deleteArtist(string $slug): void;

    public function buildEditViewModel(string $csrf, string $uploadCsrf, ?string $error, ?string $success): AdminDanceEditViewModel;

    public function saveSettings(array $post): void;
}
