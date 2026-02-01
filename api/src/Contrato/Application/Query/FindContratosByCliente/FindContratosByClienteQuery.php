<?php

declare(strict_types=1);

namespace App\Contrato\Application\Query\FindContratosByCliente;

final readonly class FindContratosByClienteQuery
{
    public function __construct(
        public int $clienteId,
        public bool $onlyActivos = false
    ) {
    }
}
