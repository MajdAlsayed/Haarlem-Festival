<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface AdminTicketsServiceInterface
{
    public function appSettings(): array;

    public function getCatalogPageData(): array;

    public function getSettingsPageData(): array;

    public function saveIntroText(string $text): void;

    public function getEditPageData(int $id): array;

    public function getNewPageData(): array;

    public function saveTicket(array $post): void;

    public function deleteTicket(int $id): string;

    public function deleteBulkTickets(array $ids): array;
}
