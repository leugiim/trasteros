<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class DireccionNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Dirección con ID "%d" no encontrada', $id));
    }

    public function errorCode(): string
    {
        return 'DIRECCION_NOT_FOUND';
    }
}
