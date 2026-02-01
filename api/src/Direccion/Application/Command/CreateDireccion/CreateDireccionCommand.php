<?php

declare(strict_types=1);

namespace App\Direccion\Application\Command\CreateDireccion;

final readonly class CreateDireccionCommand
{
    public function __construct(
        public string $nombreVia,
        public string $codigoPostal,
        public string $ciudad,
        public string $provincia,
        public string $pais = 'España',
        public ?string $tipoVia = null,
        public ?string $numero = null,
        public ?string $piso = null,
        public ?string $puerta = null,
        public ?float $latitud = null,
        public ?float $longitud = null
    ) {
    }
}
