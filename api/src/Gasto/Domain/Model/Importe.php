<?php

declare(strict_types=1);

namespace App\Gasto\Domain\Model;

use App\Gasto\Domain\Exception\InvalidImporteException;

final readonly class Importe
{
    private function __construct(
        public float $value
    ) {
        if ($value < 0) {
            throw InvalidImporteException::negative($value);
        }

        if ($value > 9999999.99) {
            throw InvalidImporteException::tooLarge($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(Importe $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function isGreaterThan(Importe $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(Importe $other): bool
    {
        return $this->value < $other->value;
    }

    public function add(Importe $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(Importe $other): self
    {
        return new self($this->value - $other->value);
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '');
    }
}
