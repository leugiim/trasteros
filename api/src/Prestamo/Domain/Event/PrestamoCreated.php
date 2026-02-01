<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Event;

use App\Prestamo\Domain\Model\PrestamoId;

final readonly class PrestamoCreated
{
    public function __construct(
        public PrestamoId $prestamoId,
        public int $localId,
        public float $capitalSolicitado,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(
        PrestamoId $prestamoId,
        int $localId,
        float $capitalSolicitado
    ): self {
        return new self(
            prestamoId: $prestamoId,
            localId: $localId,
            capitalSolicitado: $capitalSolicitado,
            occurredOn: new \DateTimeImmutable()
        );
    }
}
