<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Event;

final readonly class TrasteroUpdated
{
    public function __construct(
        public int $trasteroId,
        public int $localId,
        public string $numero
    ) {
    }
}
