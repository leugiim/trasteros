<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\FindPrestamosByLocal;

final readonly class FindPrestamosByLocalQuery
{
    public function __construct(
        public int $localId
    ) {
    }
}
