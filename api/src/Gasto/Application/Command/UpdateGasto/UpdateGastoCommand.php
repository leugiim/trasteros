<?php

declare(strict_types=1);

namespace App\Gasto\Application\Command\UpdateGasto;

final readonly class UpdateGastoCommand
{
    public function __construct(
        public int $id,
        public int $localId,
        public string $concepto,
        public float $importe,
        public string $fecha,
        public string $categoria,
        public ?string $descripcion = null,
        public ?string $metodoPago = null
    ) {
    }
}
