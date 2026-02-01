<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Exception;

final class InvalidMetodoPagoException extends \DomainException
{
    public static function withValue(string $value): self
    {
        return new self(sprintf('Invalid metodo pago: %s. Valid values are: efectivo, transferencia, tarjeta, bizum', $value));
    }
}
