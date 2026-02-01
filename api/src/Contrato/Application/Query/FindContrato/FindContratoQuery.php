<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContrato;

final readonly class FindContratoQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
