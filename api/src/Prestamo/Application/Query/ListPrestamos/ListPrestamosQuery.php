<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\ListPrestamos;

final readonly class ListPrestamosQuery
{
    public function __construct(
        public ?int $localId = null,
        public ?string $estado = null,
        public ?string $entidadBancaria = null,
        public ?bool $onlyActive = null
    ) {
    }
}
