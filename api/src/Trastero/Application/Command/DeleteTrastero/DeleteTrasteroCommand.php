<?php

declare(strict_types=1);

namespace App\Trastero\Application\Command\DeleteTrastero;

final readonly class DeleteTrasteroCommand
{
    public function __construct(
        public int $id
    ) {
    }
}
