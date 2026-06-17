<?php

declare(strict_types=1);

namespace App\Contracts\ServiceInterface;

interface EventTypeServiceInterface
{
    public function getAllWithDisplay(): array;
}
