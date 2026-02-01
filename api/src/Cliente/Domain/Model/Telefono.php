<?php

declare(strict_types=1);

namespace App\Cliente\Domain\Model;

use App\Cliente\Domain\Exception\InvalidTelefonoException;

final readonly class Telefono
{
    private const TELEFONO_PATTERN = '/^[+]?[0-9\s\-()]{9,20}$/';

    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $telefono): self
    {
        $cleaned = trim($telefono);

        if (empty($cleaned)) {
            throw InvalidTelefonoException::empty();
        }

        if (!preg_match(self::TELEFONO_PATTERN, $cleaned)) {
            throw InvalidTelefonoException::invalidFormat($telefono);
        }

        if (strlen($cleaned) > 20) {
            throw InvalidTelefonoException::tooLong($telefono);
        }

        return new self($cleaned);
    }

    public function equals(Telefono $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
