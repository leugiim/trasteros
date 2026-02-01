<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Exception;

final class IngresoNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Ingreso with id %d not found', $id));
    }
}
