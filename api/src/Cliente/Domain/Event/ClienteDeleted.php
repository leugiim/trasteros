<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Event;

final readonly class ClienteDeleted
{
    private function __construct(
        public int $clienteId,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(int $clienteId): self
    {
        return new self(
            clienteId: $clienteId,
            occurredOn: new \DateTimeImmutable()
        );
    }
}
