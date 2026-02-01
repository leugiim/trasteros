<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Command\DeleteIngreso;

final readonly class DeleteIngresoCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
