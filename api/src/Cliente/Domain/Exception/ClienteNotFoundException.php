<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

final class ClienteNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Cliente con ID %d no encontrado', $id));
    }

    public static function withDniNie(string $dniNie): self
    {
        return new self(sprintf('Cliente con DNI/NIE %s no encontrado', $dniNie));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('Cliente con email %s no encontrado', $email));
    }
}
