<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Event;

final readonly class ContratoFinalizado
{
    public function __construct(
        public int $contratoId,
        public int $trasteroId
    ) {
    }
}
