<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class InvalidCredentialsException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function create(): self
    {
        return new self('Credenciales inválidas');
    }
}
