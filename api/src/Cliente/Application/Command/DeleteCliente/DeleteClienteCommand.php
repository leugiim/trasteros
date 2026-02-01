<?php

declare(strict_types=1);

namespace App\Cliente\Application\Command\DeleteCliente;

final readonly class DeleteClienteCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
