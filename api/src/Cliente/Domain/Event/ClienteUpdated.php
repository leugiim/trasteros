<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Event;

final readonly class ClienteUpdated
{
    private function __construct(
        public int $clienteId,
        public string $nombre,
        public string $apellidos,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(
        int $clienteId,
        string $nombre,
        string $apellidos
    ): self {
        return new self(
            clienteId: $clienteId,
            nombre: $nombre,
            apellidos: $apellidos,
            occurredOn: new \DateTimeImmutable()
        );
    }
}
