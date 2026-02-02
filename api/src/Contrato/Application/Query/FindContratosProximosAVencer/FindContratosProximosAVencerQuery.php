<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosProximosAVencer;

final readonly class FindContratosProximosAVencerQuery
{
    public function __construct(
        public int $dias = 30
    ) {
    }
}
