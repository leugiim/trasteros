<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

use App\Shared\Domain\Exception\ValidationError;

final class InvalidTrasteroEstadoException extends ValidationError
{
    public static function invalidValue(string $value): self
    {
        return new self(sprintf(
            'El estado del trastero debe ser uno de: disponible, ocupado, mantenimiento, reservado. Se proporcionó: %s',
            $value
        ));
    }

    public function field(): string
    {
        return 'trasteroEstado';
    }
}
