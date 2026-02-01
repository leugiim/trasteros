<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\MarcarFianzaPagada;

final readonly class MarcarFianzaPagadaCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
