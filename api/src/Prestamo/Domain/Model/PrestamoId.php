<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Model;

final readonly class PrestamoId
{
    private function __construct(
        public int $value
    ) {
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function equals(PrestamoId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
