<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EventTypeRepositoryInterface;
use App\Contracts\ServiceInterface\EventTypeServiceInterface;

// festival category cards on the homepage
final class EventTypeService implements EventTypeServiceInterface
{
    public function __construct(
        private EventTypeRepositoryInterface $eventTypeRepository,
    ) {
    }

    public function getAllWithDisplay(): array
    {
        return $this->eventTypeRepository->getAllWithDisplay();
    }
}
