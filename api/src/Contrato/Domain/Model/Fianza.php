<?php

declare(strict_types=1);

namespace App\Contrato\Domain\Model;

use App\Contrato\Domain\Exception\InvalidFianzaException;

final readonly class Fianza
{
    private function __construct(
        public float $value
    ) {
        if ($value < 0) {
            throw new InvalidFianzaException('La fianza no puede ser negativa');
        }

        if ($value > 999999.99) {
            throw new InvalidFianzaException('La fianza no puede superar 999999.99');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '');
    }
}
