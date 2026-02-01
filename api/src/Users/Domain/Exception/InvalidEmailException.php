<?php

declare(strict_types=1);

namespace App\Users\Domain\Exception;

final class InvalidEmailException extends \DomainException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('The email "%s" is not valid', $email));
    }
}
