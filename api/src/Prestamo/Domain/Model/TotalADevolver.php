<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Model;

use App\Prestamo\Domain\Exception\InvalidTotalADevolverException;

final readonly class TotalADevolver
{
    private function __construct(
        public float $value
    ) {
        if ($value <= 0) {
            throw InvalidTotalADevolverException::negative($value);
        }

        if ($value > 999999999.99) {
            throw InvalidTotalADevolverException::tooLarge($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(TotalADevolver $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function isGreaterThan(TotalADevolver $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(TotalADevolver $other): bool
    {
        return $this->value < $other->value;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '');
    }
}
