<?php

declare(strict_types=1);

namespace App\Cliente\Application\Query\FindCliente;

final readonly class FindClienteQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
