<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

final class InvalidTotalADevolverException extends \InvalidArgumentException
{
    public static function negative(float $value): self
    {
        return new self(sprintf('El total a devolver no puede ser negativo o cero: %f', $value));
    }

    public static function tooLarge(float $value): self
    {
        return new self(sprintf('El total a devolver es demasiado grande: %f. Máximo: 999999999.99', $value));
    }
}
