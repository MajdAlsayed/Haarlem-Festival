<?php

namespace App\Services;

use App\Contracts\EventRepositoryInterface;
use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Models\Event;
use App\Validation\Validator;

/** Event listing by category/day; validates input then delegates to repo. */
class EventService implements EventServiceInterface
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {
    }

    /** @return Event[] */
    public function getAll(): array
    {
        return $this->eventRepository->getAll();
    }

    /** @return Event[] */
    public function getByCategory(string $eventTypeName): array
    {
        Validator::validateEventCategory($eventTypeName);
        return $this->eventRepository->getByCategory(trim($eventTypeName));
    }

    /** @return Event[] */
    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array
    {
        Validator::validateEventCategory($eventTypeName);
        return $this->eventRepository->getByCategoryAndDay(trim($eventTypeName), trim($eventDay));
    }

    public function getById(int $id): ?Event
    {
        return $this->eventRepository->getById($id);
    }
}
