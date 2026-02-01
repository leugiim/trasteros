<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\CancelarContrato;

final readonly class CancelarContratoCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
