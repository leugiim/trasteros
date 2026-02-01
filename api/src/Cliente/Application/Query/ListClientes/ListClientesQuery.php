<?php

declare(strict_types=1);

namespace App\Cliente\Application\Query\ListClientes;

final readonly class ListClientesQuery
{
    public function __construct(
        public ?string $search = null,
        public ?bool $onlyActivos = null
    ) {
    }
}
