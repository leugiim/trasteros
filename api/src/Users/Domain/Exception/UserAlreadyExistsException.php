<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

use App\Shared\Domain\Exception\ConflictError;

final class UserAlreadyExistsException extends ConflictError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" already exists', $email));
    }

    public function errorCode(): string
    {
        return 'USER_ALREADY_EXISTS';
    }
}
