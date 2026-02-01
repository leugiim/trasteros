<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Model;

use App\Cliente\Domain\Exception\InvalidEmailException;

final readonly class Email
{
    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $email): self
    {
        $cleaned = trim($email);

        if (empty($cleaned)) {
            throw InvalidEmailException::empty();
        }

        if (!filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalidFormat($email);
        }

        if (strlen($cleaned) > 255) {
            throw InvalidEmailException::tooLong($email);
        }

        return new self(strtolower($cleaned));
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
