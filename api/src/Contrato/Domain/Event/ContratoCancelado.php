<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Event;

final readonly class ContratoCancelado
{
    public function __construct(
        public int $contratoId,
        public int $trasteroId
    ) {
    }
}
