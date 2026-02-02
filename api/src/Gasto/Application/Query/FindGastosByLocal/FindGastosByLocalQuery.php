<?php

declare(strict_types=1);

namespace App\Gasto\Application\Query\FindGastosByLocal;

final readonly class FindGastosByLocalQuery
{
    public function __construct(
        public int $localId
    ) {
    }
}
