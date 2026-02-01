<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\FindTrastero;

final readonly class FindTrasteroQuery
{
    public function __construct(
        public int $id
    ) {
    }
}
