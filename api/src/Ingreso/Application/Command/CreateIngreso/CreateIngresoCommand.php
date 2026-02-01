<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Command\CreateIngreso;

final readonly class CreateIngresoCommand
{
    public function __construct(
        public int $contratoId,
        public string $concepto,
        public float $importe,
        public string $fechaPago,
        public string $categoria,
        public ?string $metodoPago = null
    ) {
    }
}
