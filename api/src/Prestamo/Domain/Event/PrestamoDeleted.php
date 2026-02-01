<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Event;

use App\Prestamo\Domain\Model\PrestamoId;

final readonly class PrestamoDeleted
{
    public function __construct(
        public PrestamoId $prestamoId,
        public \DateTimeImmutable $occurredOn
    ) {
    }

    public static function create(PrestamoId $prestamoId): self
    {
        return new self(
            prestamoId: $prestamoId,
            occurredOn: new \DateTimeImmutable()
        );
    }
}
