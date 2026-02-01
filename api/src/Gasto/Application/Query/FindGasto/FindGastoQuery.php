<?php

declare(strict_types=1);

namespace App\Gasto\Application\Query\FindGasto;

final readonly class FindGastoQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
