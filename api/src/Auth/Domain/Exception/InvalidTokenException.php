<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidTokenException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function missing(): self
    {
        return new self('Token de autenticación no proporcionado');
    }

    public static function invalid(): self
    {
        return new self('Token de autenticación inválido');
    }

    public static function expired(): self
    {
        return new self('Token de autenticación expirado');
    }

    public static function malformed(): self
    {
        return new self('Formato de token inválido');
    }
}
