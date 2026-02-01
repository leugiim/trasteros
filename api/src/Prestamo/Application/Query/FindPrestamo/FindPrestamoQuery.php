<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Query\FindPrestamo;

final readonly class FindPrestamoQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
