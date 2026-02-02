<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\FindTrasterosByLocal;

final readonly class FindTrasterosByLocalQuery
{
    public function __construct(
        public int $localId
    ) {
    }
}
