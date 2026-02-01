<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Command\CreatePrestamo;

final readonly class CreatePrestamoCommand
{
    public function __construct(
        public int $localId,
        public float $capitalSolicitado,
        public float $totalADevolver,
        public string $fechaConcesion,
        public ?string $entidadBancaria = null,
        public ?string $numeroPrestamo = null,
        public ?float $tipoInteres = null,
        public string $estado = 'activo'
    ) {
    }
}
