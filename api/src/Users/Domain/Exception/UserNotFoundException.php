<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

final class UserNotFoundException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withId(string $id): self
    {
        return new self(sprintf('User with id "%s" not found', $id));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" not found', $email));
    }
}
