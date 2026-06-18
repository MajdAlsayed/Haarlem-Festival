<?php

namespace App\Services;

use App\Contracts\EventRepositoryInterface;
use App\Contracts\ServiceInterface\EventServiceInterface;
use App\Models\Event;
use App\Validation\Validator;

// shared event queries — dance schedule, jazz lists, etc
class EventService implements EventServiceInterface
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {
    }

    // every event row
    public function getAll(): array
    {
        return $this->eventRepository->getAll();
    }

    // e.g. all dance events
    public function getByCategory(string $eventTypeName): array
    {
        Validator::validateEventCategory($eventTypeName);
        return $this->eventRepository->getByCategory(trim($eventTypeName));
    }

    // filter by category + friday/saturday/sunday
    public function getByCategoryAndDay(string $eventTypeName, string $eventDay): array
    {
        Validator::validateEventCategory($eventTypeName);
        return $this->eventRepository->getByCategoryAndDay(trim($eventTypeName), trim($eventDay));
    }

    // single event or null
    public function getById(int $id): ?Event
    {
        if ($id <= 0) {
            return null;
        }
        return $this->eventRepository->getById($id);
    }
}
