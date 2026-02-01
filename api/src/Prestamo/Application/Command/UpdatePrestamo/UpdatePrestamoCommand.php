<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Command\UpdatePrestamo;

final readonly class UpdatePrestamoCommand
{
    public function __construct(
        public int $id,
        public int $localId,
        public float $capitalSolicitado,
        public float $totalADevolver,
        public string $fechaConcesion,
        public ?string $entidadBancaria,
        public ?string $numeroPrestamo,
        public ?float $tipoInteres,
        public string $estado
    ) {
    }
}
