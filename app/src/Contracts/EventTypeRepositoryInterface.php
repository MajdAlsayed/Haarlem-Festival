<?php

declare(strict_types=1);

namespace App\Contracts;

interface EventTypeRepositoryInterface
{
    public function getAllWithDisplay(): array;
}
