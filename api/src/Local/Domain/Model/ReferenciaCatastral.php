<?php

declare(strict_types=1);

namespace App\Local\Domain\Model;

use App\Local\Domain\Exception\InvalidReferenciaCatastralException;

final readonly class ReferenciaCatastral
{
    private function __construct(
        public string $value
    ) {
    }

    public static function fromString(string $referencia): self
    {
        $cleaned = trim($referencia);

        if (empty($cleaned)) {
            throw InvalidReferenciaCatastralException::empty();
        }

        if (strlen($cleaned) > 50) {
            throw InvalidReferenciaCatastralException::tooLong($cleaned);
        }

        return new self($cleaned);
    }

    public function equals(ReferenciaCatastral $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
