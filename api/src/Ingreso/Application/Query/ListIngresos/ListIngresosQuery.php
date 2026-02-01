<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\ListIngresos;

final readonly class ListIngresosQuery
{
    public function __construct(
        public ?int $contratoId = null,
        public ?string $categoria = null,
        public ?string $desde = null,
        public ?string $hasta = null,
        public ?bool $onlyActive = null
    ) {
    }
}
