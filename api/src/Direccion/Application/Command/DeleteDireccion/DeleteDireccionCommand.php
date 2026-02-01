<?php

declare(strict_types=1);

namespace App\Direccion\Application\Command\DeleteDireccion;

final readonly class DeleteDireccionCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
