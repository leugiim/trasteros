<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Exception;

final class InvalidTrasteroEstadoException extends \InvalidArgumentException
{
    public static function invalidValue(string $value): self
    {
        return new self(sprintf(
            'El estado del trastero debe ser uno de: disponible, ocupado, mantenimiento, reservado. Se proporcionó: %s',
            $value
        ));
    }
}
