<?php

declare(strict_types=1);

namespace App\Trastero\Application\Query\ListTrasteros;

final readonly class ListTrasterosQuery
{
    public function __construct(
        public ?int $localId = null,
        public ?string $estado = null,
        public ?bool $onlyActive = null
    ) {
    }
}
