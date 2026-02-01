<?php

declare(strict_types=1);

namespace App\Cliente\Application\Command\CreateCliente;

final readonly class CreateClienteCommand
{
    public function __construct(
        public string $nombre,
        public string $apellidos,
        public ?string $dniNie = null,
        public ?string $email = null,
        public ?string $telefono = null,
        public bool $activo = true
    ) {
    }
}
