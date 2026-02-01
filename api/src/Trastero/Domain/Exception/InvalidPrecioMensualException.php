<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

final class InvalidPrecioMensualException extends \InvalidArgumentException
{
    public static function negative(float $value): self
    {
        return new self(sprintf('El precio mensual no puede ser negativo, se proporcionó: %.2f', $value));
    }

    public static function tooLarge(float $value): self
    {
        return new self(sprintf('El precio mensual no puede superar 99999999.99, se proporcionó: %.2f', $value));
    }
}
