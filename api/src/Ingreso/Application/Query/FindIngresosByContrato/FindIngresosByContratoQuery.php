<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngresosByContrato;

final readonly class FindIngresosByContratoQuery
{
    public function __construct(
        public int $contratoId
    ) {
    }
}
