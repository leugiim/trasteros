<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

final class UserInactiveException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('El usuario "%s" está desactivado', $email));
    }
}
