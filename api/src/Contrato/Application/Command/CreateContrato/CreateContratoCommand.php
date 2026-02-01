<?php

declare(strict_types=1);

namespace App\Contrato\Application\Command\CreateContrato;

final readonly class CreateContratoCommand
{
    public function __construct(
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
