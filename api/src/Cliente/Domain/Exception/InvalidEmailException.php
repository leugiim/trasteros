<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

final class InvalidEmailException extends \InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('El email no puede estar vacío');
    }

    public static function invalidFormat(string $email): self
    {
        return new self(sprintf('El email "%s" no tiene un formato válido', $email));
    }

    public static function tooLong(string $email): self
    {
        return new self(sprintf('El email "%s" supera los 255 caracteres', $email));
    }
}
