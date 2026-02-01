<?php

declare(strict_types=1);

namespace App\Prestamo\Application\Command\DeletePrestamo;

final readonly class DeletePrestamoCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
