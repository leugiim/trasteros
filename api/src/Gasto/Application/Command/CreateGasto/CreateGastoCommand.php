<?php

declare(strict_types=1);

namespace App\Gasto\Application\Command\CreateGasto;

final readonly class CreateGastoCommand
{
    public function __construct(
        public int $localId,
        public string $concepto,
        public float $importe,
        public string $fecha,
        public string $categoria,
        public ?string $descripcion = null,
        public ?string $metodoPago = null,
        public ?int $prestamoId = null
    ) {
    }
}
