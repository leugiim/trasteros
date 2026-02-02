<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngresosByLocal;

final readonly class FindIngresosByLocalQuery
{
    public function __construct(
        public int $localId
    ) {
    }
}
