<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Event;

final readonly class TrasteroEstadoChanged
{
    public function __construct(
        public int $trasteroId,
        public string $previousEstado,
        public string $newEstado
    ) {
    }
}
