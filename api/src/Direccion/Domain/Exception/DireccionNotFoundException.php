<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Exception;

final class DireccionNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Dirección con ID "%d" no encontrada', $id));
    }
}
