<?php

declare(strict_types=1);

namespace App\Gasto\Application\Command\DeleteGasto;

final readonly class DeleteGastoCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
