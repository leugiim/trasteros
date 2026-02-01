<?php

declare(strict_types=1);

namespace App\Local\Application\Query\ListLocales;

final readonly class ListLocalesQuery
{
    public function __construct(
        public ?string $nombre = null,
        public ?int $direccionId = null,
        public ?bool $onlyActive = null
    ) {
    }
}
