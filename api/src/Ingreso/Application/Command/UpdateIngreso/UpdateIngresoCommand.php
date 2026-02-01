<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Command\UpdateIngreso;

final readonly class UpdateIngresoCommand
{
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $concepto,
        public float $importe,
        public string $fechaPago,
        public string $categoria,
        public ?string $metodoPago = null
    ) {
    }
}
