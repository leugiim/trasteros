<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class GastoNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Gasto with id %d not found', $id));
    }

    public function errorCode(): string
    {
        return 'GASTO_NOT_FOUND';
    }
}
