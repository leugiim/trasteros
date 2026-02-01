<?php

declare(strict_types=1);

namespace App\Local\Domain\Exception;

final class InvalidReferenciaCatastralException extends \InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('La referencia catastral no puede estar vacía');
    }

    public static function tooLong(string $referencia): self
    {
        return new self(sprintf(
            'La referencia catastral "%s" no puede superar los 50 caracteres',
            $referencia
        ));
    }
}
