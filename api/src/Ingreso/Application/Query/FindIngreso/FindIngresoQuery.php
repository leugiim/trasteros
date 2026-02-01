<?php

declare(strict_types=1);

namespace App\Ingreso\Application\Query\FindIngreso;

final readonly class FindIngresoQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
