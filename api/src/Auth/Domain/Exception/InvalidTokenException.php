<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\AuthenticationError;

final class InvalidTokenException extends AuthenticationError
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

    public function errorCode(): string
    {
        return 'UNAUTHORIZED';
    }
}
