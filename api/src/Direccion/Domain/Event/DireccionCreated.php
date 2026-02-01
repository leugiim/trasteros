<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Event;

final readonly class DireccionCreated
{
    private function __construct(
        public int $direccionId,
        public string $nombreVia,
        public string $ciudad,
        public string $provincia,
        public string $codigoPostal
    ) {
    }

    public static function create(
        int $direccionId,
        string $nombreVia,
        string $ciudad,
        string $provincia,
        string $codigoPostal
    ): self {
        return new self($direccionId, $nombreVia, $ciudad, $provincia, $codigoPostal);
    }
}
