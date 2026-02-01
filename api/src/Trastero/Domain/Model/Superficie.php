<?php

declare(strict_types=1);

namespace App\Trastero\Domain\Model;

use App\Trastero\Domain\Exception\InvalidSuperficieException;

final readonly class Superficie
{
    private function __construct(
        public float $value
    ) {
        if ($value <= 0) {
            throw InvalidSuperficieException::notPositive($value);
        }

        if ($value > 9999.99) {
            throw InvalidSuperficieException::tooLarge($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(Superficie $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function isGreaterThan(Superficie $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(Superficie $other): bool
    {
        return $this->value < $other->value;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '') . ' m²';
    }
}
