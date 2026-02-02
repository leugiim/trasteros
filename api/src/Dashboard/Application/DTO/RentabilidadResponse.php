<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTO;

final readonly class RentabilidadResponse
{
    public function __construct(
        public array $locales
    ) {
    }

    public function toArray(): array
    {
        return [
            'locales' => $this->locales,
        ];
    }
}
