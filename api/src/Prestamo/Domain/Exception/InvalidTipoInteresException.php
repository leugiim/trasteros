<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidTipoInteresException extends ValidationError
{
    public static function negative(float $value): self
    {
        return new self(sprintf('El tipo de interés no puede ser negativo: %f', $value));
    }

    public static function tooLarge(float $value): self
    {
        return new self(sprintf('El tipo de interés es demasiado grande: %f. Máximo: 99.9999', $value));
    }

    public function field(): string
    {
        return 'tipoInteres';
    }
}
