<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

final class UserAlreadyExistsException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" already exists', $email));
    }
}
