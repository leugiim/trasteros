<?php

declare(strict_types=1);

namespace App\Local\Domain\Event;

final readonly class LocalCreated
{
    private function __construct(
        public int $localId,
        public string $nombre,
        public int $direccionId
    ) {
    }

    public static function create(
        int $localId,
        string $nombre,
        int $direccionId
    ): self {
        return new self($localId, $nombre, $direccionId);
    }
}
