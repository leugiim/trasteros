<?php

declare(strict_types=1);

namespace App\Gasto\Application\Query\ListGastos;

final readonly class ListGastosQuery
{
    public function __construct(
        public ?int $localId = null,
        public ?string $categoria = null,
        public ?string $desde = null,
        public ?string $hasta = null,
        public ?bool $onlyActive = null
    ) {
    }
}
