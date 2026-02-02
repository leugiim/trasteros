<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngresosByTrastero;

final readonly class FindIngresosByTrasteroQuery
{
    public function __construct(
        public int $trasteroId
    ) {
    }
}
