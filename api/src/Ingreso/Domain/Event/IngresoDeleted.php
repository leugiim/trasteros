<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Event;

final readonly class IngresoDeleted
{
    public function __construct(
        public int $ingresoId,
        public int $contratoId
    ) {
    }
}
