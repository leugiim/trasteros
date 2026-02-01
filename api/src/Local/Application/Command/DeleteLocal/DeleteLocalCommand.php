<?php

declare(strict_types=1);

namespace App\Local\Application\Command\DeleteLocal;

final readonly class DeleteLocalCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
