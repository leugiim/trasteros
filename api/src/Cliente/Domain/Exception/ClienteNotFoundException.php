<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundError;

final class ClienteNotFoundException extends NotFoundError
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

    public function errorCode(): string
    {
        return 'CLIENTE_NOT_FOUND';
    }
}
