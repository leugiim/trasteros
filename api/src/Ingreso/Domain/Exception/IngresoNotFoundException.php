<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class IngresoNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Ingreso with id %d not found', $id));
    }

    public function errorCode(): string
    {
        return 'INGRESO_NOT_FOUND';
    }
}
