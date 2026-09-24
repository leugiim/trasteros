<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\AuthenticationError;

final class InvalidCredentialsException extends AuthenticationError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(): self
    {
        return new self('Credenciales inválidas');
    }

    public function errorCode(): string
    {
        return 'INVALID_CREDENTIALS';
    }
}
