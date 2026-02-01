<?php

declare(strict_types=1);

namespace App\Users\Domain\Model;

use App\Users\Domain\Exception\InvalidEmailException;

final readonly class UserEmail
{
    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $email): self
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::withEmail($email);
        }

        return new self(strtolower($email));
    }

    public function equals(UserEmail $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
