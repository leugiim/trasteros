<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Event;

final readonly class IngresoUpdated
{
    public function __construct(
        public int $ingresoId,
        public int $contratoId,
        public string $concepto,
        public float $importe,
        public string $categoria
    ) {
    }
}
