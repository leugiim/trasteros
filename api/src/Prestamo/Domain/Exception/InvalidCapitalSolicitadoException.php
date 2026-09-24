<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidCapitalSolicitadoException extends ValidationError
{
    public static function negative(float $value): self
    {
        return new self(sprintf('El capital solicitado no puede ser negativo o cero: %f', $value));
    }

    public static function tooLarge(float $value): self
    {
        return new self(sprintf('El capital solicitado es demasiado grande: %f. Máximo: 999999999.99', $value));
    }

    public function field(): string
    {
        return 'capitalSolicitado';
    }
}
