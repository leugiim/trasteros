<?php

declare(strict_types=1);

namespace App\Ingreso\Domain\Exception;

final class InvalidImporteException extends \DomainException
{
    public static function negative(float $value): self
    {
        return new self(sprintf('Importe cannot be negative: %.2f', $value));
    }

    public static function tooLarge(float $value): self
    {
        return new self(sprintf('Importe is too large: %.2f (max: 99999.99)', $value));
    }
}
