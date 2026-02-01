<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosByTrastero;

final readonly class FindContratosByTrasteroQuery
{
    public function __construct(
        public int $trasteroId,
        public bool $onlyActivos = false
    ) {
    }
}
