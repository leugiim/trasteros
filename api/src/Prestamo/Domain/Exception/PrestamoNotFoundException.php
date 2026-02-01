<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

final class PrestamoNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Prestamo with id %d not found', $id));
    }
}
