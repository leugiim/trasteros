<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Model;

final readonly class ContratoId
{
    private function __construct(
        public int $value
    ) {
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
