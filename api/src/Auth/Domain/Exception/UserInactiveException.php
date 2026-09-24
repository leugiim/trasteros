<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\ForbiddenError;

final class UserInactiveException extends ForbiddenError
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('El usuario "%s" está desactivado', $email));
    }

    public function errorCode(): string
    {
        return 'USER_INACTIVE';
    }
}
