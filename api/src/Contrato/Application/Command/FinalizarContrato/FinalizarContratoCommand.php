<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\FinalizarContrato;

final readonly class FinalizarContratoCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
