<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Exception;

final class InvalidIngresoCategoriaException extends \DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('Invalid ingreso categoria: %s. Valid values are: mensualidad, fianza, penalizacion, otros', $value));
    }
}
