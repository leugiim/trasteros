<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\AuthenticationError;

final class InvalidRefreshTokenException extends AuthenticationError
{
    private function __construct(
        string $message,
        private readonly string $errorCode,
    ) {
        parent::__construct($message);
    }

    public static function invalid(): self
    {
        return new self('Refresh token inválido', 'INVALID_REFRESH_TOKEN');
    }

    public static function expired(): self
    {
        return new self('Refresh token expirado', 'EXPIRED_REFRESH_TOKEN');
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
