<?php

declare(strict_types=1);

namespace App\Direccion\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidCodigoPostalException extends ValidationError
{
    public static function empty(): self
    {
        return new self('El código postal no puede estar vacío');
    }

    public static function tooLong(string $codigoPostal): self
    {
        return new self(sprintf('El código postal "%s" supera la longitud máxima de 10 caracteres', $codigoPostal));
    }

    public function field(): string
    {
        return 'codigoPostal';
    }
}
