<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Model;

use App\Trastero\Domain\Exception\InvalidPrecioMensualException;

final readonly class PrecioMensual
{
    private function __construct(
        public float $value
    ) {
        if ($value < 0) {
            throw InvalidPrecioMensualException::negative($value);
        }

        if ($value > 99999999.99) {
            throw InvalidPrecioMensualException::tooLarge($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(PrecioMensual $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function isGreaterThan(PrecioMensual $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(PrecioMensual $other): bool
    {
        return $this->value < $other->value;
    }

    public function multiply(int|float $factor): self
    {
        return new self($this->value * $factor);
    }

    public function calculateAnual(): self
    {
        return new self($this->value * 12);
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '') . ' €';
    }
}
