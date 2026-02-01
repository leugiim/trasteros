<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\UpdateContrato;

final readonly class UpdateContratoCommand
{
    public function __construct(
        public int $id,
        public int $trasteroId,
        public int $clienteId,
        public string $fechaInicio,
        public float $precioMensual,
        public ?string $fechaFin = null,
        public ?float $fianza = null,
        public bool $fianzaPagada = false,
        public string $estado = 'activo'
    ) {
    }
}
