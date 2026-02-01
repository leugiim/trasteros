<?php

declare(strict_types=1);

namespace App\Direccion\Application\Query\FindDireccion;

final readonly class FindDireccionQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
