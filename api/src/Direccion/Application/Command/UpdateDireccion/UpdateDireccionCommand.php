<?php

declare(strict_types=1);

namespace App\Direccion\Application\Command\UpdateDireccion;

final readonly class UpdateDireccionCommand
{
    public function __construct(
        public int $id,
        public string $nombreVia,
        public string $codigoPostal,
        public string $ciudad,
        public string $provincia,
        public string $pais,
        public ?string $tipoVia,
        public ?string $numero,
        public ?string $piso,
        public ?string $puerta,
        public ?float $latitud,
        public ?float $longitud
    ) {
    }
}
