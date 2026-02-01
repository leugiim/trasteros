<?php

declare(strict_types=1);

namespace App\Direccion\Application\Query\ListDirecciones;

final readonly class ListDireccionesQuery
{
    public function __construct(
        public ?string $ciudad = null,
        public ?string $provincia = null,
        public ?string $codigoPostal = null,
        public ?bool $onlyActive = null
    ) {
    }
}
