<?php

declare(strict_types=1);

namespace App\Trastero\Application\Command\CreateTrastero;

final readonly class CreateTrasteroCommand
{
    public function __construct(
        public int $localId,
        public string $numero,
        public float $superficie,
        public float $precioMensual,
        public ?string $nombre = null,
        public string $estado = 'disponible'
    ) {
    }
}
