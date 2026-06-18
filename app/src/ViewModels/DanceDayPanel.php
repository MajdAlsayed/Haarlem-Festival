<?php

declare(strict_types=1);

namespace App\ViewModels;

final class DanceDayPanel
{

    public function __construct(
        public string $panelClass,
        public array $cards,
    ) {
    }
}
