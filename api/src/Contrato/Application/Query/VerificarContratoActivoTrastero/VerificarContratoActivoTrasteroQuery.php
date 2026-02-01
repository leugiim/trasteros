<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\VerificarContratoActivoTrastero;

final readonly class VerificarContratoActivoTrasteroQuery
{
    public function __construct(
        public int $trasteroId
    ) {
    }
}
