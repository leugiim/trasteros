<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class PrestamoNotFoundException extends NotFoundError
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Prestamo with id %d not found', $id));
    }

    public function errorCode(): string
    {
        return 'PRESTAMO_NOT_FOUND';
    }
}
