<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

final class InvalidDniNieException extends \InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('El DNI/NIE no puede estar vacío');
    }

    public static function invalidFormat(string $dniNie): self
    {
        return new self(sprintf('El DNI/NIE "%s" tiene un formato inválido. Debe tener 8 dígitos y una letra', $dniNie));
    }

    public static function invalidCheckLetter(string $dniNie): self
    {
        return new self(sprintf('El DNI/NIE "%s" tiene una letra de control inválida', $dniNie));
    }
}
