<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\ListContratos;

final readonly class ListContratosQuery
{
    public function __construct(
        public ?string $estado = null
    ) {
    }
}
