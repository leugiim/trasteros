<?php

declare(strict_types=1);

namespace App\Prestamo\Domain\Model;

use App\Prestamo\Domain\Exception\InvalidCapitalSolicitadoException;

final readonly class CapitalSolicitado
{
    private function __construct(
        public float $value
    ) {
        if ($value <= 0) {
            throw InvalidCapitalSolicitadoException::negative($value);
        }

        if ($value > 999999999.99) {
            throw InvalidCapitalSolicitadoException::tooLarge($value);
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function equals(CapitalSolicitado $other): bool
    {
        return abs($this->value - $other->value) < 0.01;
    }

    public function isGreaterThan(CapitalSolicitado $other): bool
    {
        return $this->value > $other->value;
    }

    public function isLessThan(CapitalSolicitado $other): bool
    {
        return $this->value < $other->value;
    }

    public function __toString(): string
    {
        return number_format($this->value, 2, '.', '');
    }
}
