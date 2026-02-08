<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidRefreshTokenException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function invalid(): self
    {
        return new self('Refresh token inválido');
    }

    public static function expired(): self
    {
        return new self('Refresh token expirado');
    }
}
