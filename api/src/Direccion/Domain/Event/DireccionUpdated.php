<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Event;

final readonly class DireccionUpdated
{
    private function __construct(
        public int $direccionId
    ) {
    }

    public static function create(int $direccionId): self
    {
        return new self($direccionId);
    }
}
