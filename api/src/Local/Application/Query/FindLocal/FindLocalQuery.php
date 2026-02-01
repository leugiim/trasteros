<?php

declare(strict_types=1);

namespace App\Local\Application\Query\FindLocal;

final readonly class FindLocalQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
