<?php

namespace App\Services;

use App\Contracts\EventRepositoryInterface;
use App\Models\Event;
use App\Validation\Validator;

class EventService
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
}
