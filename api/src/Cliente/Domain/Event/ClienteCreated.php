<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Event;

final readonly class ClienteCreated
{
    private function __construct(
        public int $clienteId,
        public string $nombre,
        public string $apellidos,
        public ?string $email,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(
        int $clienteId,
        string $nombre,
        string $apellidos,
        ?string $email
    ): self {
        return new self(
            clienteId: $clienteId,
            nombre: $nombre,
            apellidos: $apellidos,
            email: $email,
            occurredOn: new \DateTimeImmutable()
        );
    }
}
