<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\DeleteContrato;

final readonly class DeleteContratoCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
