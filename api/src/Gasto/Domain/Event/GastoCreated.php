<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Event;

use App\Gasto\Domain\Model\GastoId;

final readonly class GastoCreated
{
    public function __construct(
        public GastoId $gastoId,
        public int $localId,
        public float $importe,
        public string $categoria,
        public \DateTimeImmutable $fecha,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(
        GastoId $gastoId,
        int $localId,
        float $importe,
        string $categoria,
        \DateTimeImmutable $fecha
    ): self {
        return new self(
            gastoId: $gastoId,
            localId: $localId,
            importe: $importe,
            categoria: $categoria,
            fecha: $fecha,
            occurredOn: new \DateTimeImmutable()
        );
    }
}
