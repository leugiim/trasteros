<?php

declare(strict_types=1);

namespace App\Trastero\Application\Command\UpdateTrastero;

final readonly class UpdateTrasteroCommand
{
    public function __construct(
        public int $id,
        public int $localId,
        public string $numero,
        public float $superficie,
        public float $precioMensual,
        public ?string $nombre,
        public string $estado
    ) {
    }
}
