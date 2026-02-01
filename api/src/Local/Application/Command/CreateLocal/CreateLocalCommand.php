<?php

declare(strict_types=1);

namespace App\Local\Application\Command\CreateLocal;

final readonly class CreateLocalCommand
{
    public function __construct(
        public string $nombre,
        public int $direccionId,
        public ?float $superficieTotal = null,
        public ?int $numeroTrasteros = null,
        public ?string $fechaCompra = null,
        public ?float $precioCompra = null,
        public ?string $referenciaCatastral = null,
        public ?float $valorCatastral = null
    ) {
    }
}
