<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidTelefonoException extends ValidationError
{
    public static function empty(): self
    {
        return new self('El teléfono no puede estar vacío');
    }

    public static function invalidFormat(string $telefono): self
    {
        return new self(sprintf('El teléfono "%s" no tiene un formato válido', $telefono));
    }

    public static function tooLong(string $telefono): self
    {
        return new self(sprintf('El teléfono "%s" supera los 20 caracteres', $telefono));
    }

    public function field(): string
    {
        return 'telefono';
    }
}
